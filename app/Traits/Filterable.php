<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Filterable
{
    public function applyFiltersAndSorting(Builder $query, Request $request, $fieldTableMap = null)
    {
        // Apply dynamic filters
        $this->applyFilters($query, $request);

        // Apply dynamic searchable fields
        $this->applySearchableFields($query, $request, $fieldTableMap);

        // Apply dynamic sorting
        $this->applySorting($query, $request);

        // Apply pagination
        $perPage = $request->input('length', 10);

//        dd($query->toSql(), $query->getBindings());
        return $query->paginate($perPage);
    }

    private function applyFilters(Builder $query, Request $request)
    {
        $filters = $request->input('filters', []);
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                if ($key == 'status' || $key == 'deposit_status') {
                    $value = $this->transformStatus($key, $value);
                }
                $key =  ($key == 'deposit_status') ? 'status' : $key;
                $query->where($key, $value);
            }
        }
    }

    private function applySearchableFields(Builder $query, Request $request, $fieldTableMap)
    {
        if ($search = $request->input('search')['value'] ?? null) {
            $searchableFields = json_decode($request->input('searchableFields', []), true);

            if (!empty($searchableFields) && !empty($search)) {
                $query->where(function ($q) use ($search, $searchableFields, $fieldTableMap) {
                    foreach ($searchableFields as $field) {
                        // Transform search term if field is deposit_status or status
                        $searchModified = $this->transformStatus($field, $search);
                        $this->buildSearchQuery($q, $field, $searchModified, $fieldTableMap);

                    }
                });
            }
        }
    }


    private function buildSearchQuery($q, $field, $search, $fieldTableMap)
    {
        // Check for deposit_status and transform it to status
        if ($field === 'deposit_status') {
            $field = 'status';
        }
//        if ($field === 'address_type') {
//            $search =  ($search == "External") ?  'internal_address' : $search;
//        }
        if (isset($fieldTableMap[$field])) {
            if (is_array($fieldTableMap[$field])) {

                foreach ($fieldTableMap[$field] as $relation => $relationField) {
                    if (\Schema::hasColumn($relation, $relationField)) {
                        $q->orWhereHas($relation, function ($query) use ($relationField, $search) {
                            $query->where($relationField, 'like', "%{$search}%");
                        });
                    }
                }
            } else {
                if (\Schema::hasColumn($fieldTableMap[$field], $field)) {
                    $q->orWhere("{$fieldTableMap[$field]}.{$field}", 'like', "%{$search}%");
                }
            }
        } else {
            $q->orWhere($field, 'like', "%{$search}%");
        }
    }



    private function applySorting(Builder $query, Request $request)
    {
        $sortField = $request->input('sortField', 'created_at');
        $sortDirection = $request->input('sortDirection', 'desc');
        $query->orderBy($sortField, $sortDirection);
    }

    private function transformStatus($field, $value)
    {
        if ($field === 'deposit_status') {
            return deposit_status_opposite($value);
        } elseif ($field === 'status') {
            return status_opposite($value);
        }

        return $value;
    }
}
