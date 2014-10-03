<?php
namespace Core\Lib\Traits;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 *        
 */
trait ArrayTrait
{

    /**
     * Inserts an array ($insert) at ($position) after the key ($search) in an array ($array) and returns a combined array
     * 
     * @param array $array Array to insert the array
     * @param array $insert Array to insert inti $array
     * @param string $search Key to search and insert after
     * @param number $position Position after the found key to insert into
     * @return array
     */
    function addBySearchedKey(&$array, $search, $insert, $position = 0)
    {
        if (! is_array($array))
            throw new \InvalidArgumentException('Wrong parameter type.', 1000);
        
        $counter = 0;
        $keylist = array_keys($array);
        
        foreach ($keylist as $key) {
            if ($key == $search)
                break;
            $counter ++;
        }
        
        $counter += $position;
        
        $array = array_slice($array, 0, $counter, true) + $insert + array_slice($array, $counter, null, true);
        
        return $array;
    }

    /**
     * Slices an array at the search point and returns both slices.
     * 
     * @param array $array
     * @param string $search
     * @param number $position
     * @return array
     */
    function getSlicesBySearchedKey($array, $search, $position = 0)
    {
        if (! is_array($array))
            throw new \InvalidArgumentException('Wrong parameter type.', 1000);
        
        $counter = 0;
        $keylist = array_keys($array);
        
        foreach ($keylist as $key) {
            if ($key == $search)
                break;
            $counter ++;
        }
        
        $counter += $position;
        
        return array(
            array_slice($array, 0, $counter, true),
            array_slice($array, $counter, null, true)
        );
    }

    /**
     * Checks for an array for if it is assoc or indexed.
     * Returns true in case of assoc array and false on indexed array.
     * 
     * @param array $array
     * @return boolean
     */
    function isAssoc($array)
    {
        if (! is_array($array))
            return false;
        
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }
}
