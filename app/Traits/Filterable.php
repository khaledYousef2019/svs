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

    private function isFieldConcatenated($field, $fieldTableMap): bool
    {
        // Check if the field is part of a concatenated mapping (e.g., first_name is part of first_name.last_name)
        foreach ($fieldTableMap as $key => $value) {
            if (strpos($key, '.') !== false && in_array($field, explode('.', $key))) {
                return true;
            }
        }
        return false;
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

                        $isConcatenated = $this->isFieldConcatenated($field, $fieldTableMap);
                        if ($isConcatenated) {
                            $this->buildConcatenatedSearchQuery($q, $field, $search, $fieldTableMap);
                        } else {
                            $this->buildSearchQuery($q, $field, $search, $fieldTableMap);
                        }
                    }
                });
            }
        }
    }

    private function buildConcatenatedSearchQuery($q, $field, $search, $fieldTableMap)
    {
        // Find the concatenated field mapping (e.g., first_name.last_name => users)
        $concatenatedField = $this->findConcatenatedFieldMapping($field, $fieldTableMap);

        if ($concatenatedField) {
            $table = $fieldTableMap[$concatenatedField];
            $fields = explode('.', $concatenatedField);

            if (\Schema::hasColumns($table, $fields)) {
                $q->orWhere(function ($query) use ($table, $fields, $search) {
                    $query->whereRaw("CONCAT({$table}.{$fields[0]}, ' ', {$table}.{$fields[1]}) LIKE ?", ["%{$search}%"]);
                });
            }
        }
    }
    private function findConcatenatedFieldMapping($field, $fieldTableMap)
    {
        // Find the concatenated field mapping (e.g., first_name.last_name => users)
        foreach ($fieldTableMap as $key => $value) {
            if (strpos($key, '.') !== false && in_array($field, explode('.', $key))) {
                return $key;
            }
        }
        return null;
    }
    private function buildSearchQuery($q, $field, $search, $fieldTableMap)
    {
        // Check for deposit_status and transform it to status
        if ($field === 'deposit_status') {
            $field = 'status';
        }

        if (isset($fieldTableMap[$field])) {
            if (is_array($fieldTableMap[$field])) {
                foreach ($fieldTableMap[$field] as $relation => $relationField) {
                    // Resolve the table name from the relationship
                    $relatedModel = $q->getModel()->{$relation}()->getRelated();
                    $tableName = $relatedModel->getTable();
                    if (strpos($relationField, '.') !== false) {
                        $concatFields = explode('.', $relationField);
                        if (\Schema::hasColumns($tableName, $concatFields)) {
                            $q->orWhereHas($relation, function ($query) use ($concatFields, $search, $tableName) {
                                $query->whereRaw("CONCAT({$tableName}.{$concatFields[0]}, ' ', {$tableName}.{$concatFields[1]}) LIKE ?", ["%{$search}%"]);
                            });
                        } else {
                            \Log::error("Columns '{$relationField}' do not exist in table '{$tableName}'.");
                        }
                    }else{
                        if (\Schema::hasColumn($tableName, $relationField)) {
                            $q->orWhereHas($relation, function ($query) use ($relationField, $search) {
                                $query->where($relationField, 'like', "%{$search}%");
                            });
                        } else {
                            \Log::error("Column '{$relationField}' does not exist in table '{$tableName}'.");
                        }
                    }
                    
                }
            } else {
                if (\Schema::hasColumn($fieldTableMap[$field], $field)) {
                    $q->orWhere("{$fieldTableMap[$field]}.{$field}", 'like', "%{$search}%");
                } else {
                    \Log::error("Column '{$field}' does not exist in table '{$fieldTableMap[$field]}'.");
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
