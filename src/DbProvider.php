<?php

namespace Flagship\Components\Helpers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DbProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['flagship.helpers.pdoHydrateArray'] = $app->protect(function ($rows) {
            $z = [];
            foreach ($rows as $row) {
                foreach ($row as $k => $v) {
                    $z[$k][] = $v;
                }
            }

            array_walk($z, function (&$v, $k) {
                $v = array_unique($v);
                if (count($v) === 1) {
                    $v = $v[0];
                }

            });

            array_walk_recursive($z, function (&$v, $k) {
                $v = is_numeric($v) ? $v+0 : $v;
            });

            return count($z) > 0 ? $z : false;
        });

        /*
         * Hydrates a set of rows
         *
         * The selected columns contain a prefix of format [entityOrSubEntityPrefix]_
         * All columns belonging to the same entity or sub-entity will contain the same prefix
         *
         * Assumptions:
         *
         *     * The first prefix found in the data is considered the main entity unless $entityPrefix is specified.
         *     * The first column found with each prefix is considered as a "unique" identifier for the entity or sub-entities
         *
         * @param  array $rows
         * @param  string $entityPrefix The main entity name. If not specified, the first set of columns is considered as the main entity
         *
         * @return  array The hydrated data
         */
        $app['flagship.helpers.pdoHydrate'] = $app->protect(function ($rows, $entityPrefix = null) {
            if (count($rows) == 0) {
                return [];
            }
            // Arrange keys
            $allKeys = array_keys($rows[0]);
            // Get a map with rowsKeys => dbKey
            $keyMap = [];

            foreach ($allKeys as $item) {
                $element = substr($item, 0, strpos($item, '_'));
                $keyMap[$element][$item] = substr($item, strpos($item, '_')+1);
            }

            if (empty($entityPrefix)) {
                // Assume the first item is the entity
                $entityKeys = array_splice($keyMap, 0, 1);
                $entityKeys = reset($entityKeys);
            }

            if (!empty($entityPrefix)) {
                $entityKeys = $keyMap[$entityPrefix];
                unset($keyMap[$entityPrefix]);
            }
            $columns = array_keys($entityKeys);

            // Assume the first item is the unique identifier
            $unique = reset($columns);
            $entities = [];
            $i = 0;

            foreach ($rows as $r) {
                $i++;
                $uniquer = $r[$unique];
                if (!isset($entities[$uniquer])) {
                    $entities[$uniquer] = $this->createItem($entityKeys, $r);
                }

                foreach ($keyMap as $subName => $sub) {
                    $subColumns = array_keys($sub);
                    // Assume the first item is the unique identifier
                    $subUnique = $r[reset($subColumns)];
                    $entities[$uniquer][$subName][$subUnique] = $this->createItem($sub, $r);
                }
            }

            //clear up the values from the keys
            $entities = array_values($entities);
            foreach ($entities as $key => &$value) {
                foreach ($value as $subkey => &$subvalue) {
                    if (is_array($subvalue)) {
                        // if the subvalue key is empty(or false) make subvalue empty
                        // this is an issue when a shipment has no accountables
                        $subvalue = key($subvalue) == false ? [] : array_values($subvalue);
                    }
                }
            }

            return $entities;
        });
    }

    protected function createItem(array $itemKeys, array $row)
    {
        $item = [];
        foreach ($itemKeys as $rowKey => $dbKey) {
            $item[$dbKey] = $row[$rowKey];
        }

        return $item;
    }
}
