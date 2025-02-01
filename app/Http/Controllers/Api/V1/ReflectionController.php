<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class ReflectionController extends Controller
{
    public function reflect($className)
    {
        try {
            $class = new ReflectionClass($className);

            $methods = array_map(function (ReflectionMethod $method) {
                return [
                    'name' => $method->getName(),
                    'parameters' => array_map(function ($param) {
                        return [
                            'name' => $param->getName(),
                            'type' => $param->getType() ? $param->getType()->getName() : 'mixed',
                        ];
                    }, $method->getParameters()),
                ];
            }, $class->getMethods());

            $properties = array_map(function (ReflectionProperty $property) {
                return [
                    'name' => $property->getName(),
                    'type' => $property->getType() ? $property->getType()->getName() : 'mixed',
                ];
            }, $class->getProperties());

            return response()->json([
                'class' => $className,
                'methods' => $methods,
                'properties' => $properties,
            ]);
        } catch (\ReflectionException $e) {
            return response()->json(['error' => 'Class not found'], 404);
        }
    }
}
