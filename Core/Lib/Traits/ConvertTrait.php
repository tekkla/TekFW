<?php
namespace Core\Lib\Traits;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 *        
 */
trait ConvertTrait
{
    use\Core\Lib\Traits\SerializeTrait;

    /**
     * Converts an object and it's public members recursively into an array.
     * Use this if you want to convert objects into array.
     * 
     * @param object $obj
     * @return array
     */
    function convertObjectToArray($obj)
    {
        if (! is_object($obj))
            return $obj;
        
        $out = [];
        
        foreach ($obj as $key => $val) {
            if (is_object($val))
                $out[$key] = $this->convertToArray($val);
            else
                $out[$key] = $val;
        }
        
        return $out;
    }

    /**
     * Converts an array into an Data object.
     * This method works recursive.
     * 
     * @param array $data
     * @return Data
     */
    function convertToObject($data)
    {
        // Return $data when it is already an object
        if (is_object($data))
            return $data;
        
        $data = new \Core\Lib\Data\Data($data);
        
        foreach ($data as $key => $val) {
            if ($this->isSerialized($val))
                $val = unserialize($val);
            
            if (is_array($val))
                $val = $this->converToObject($val);
            
            $val = empty($val) && strlen($val) == 0 ? '' : $val;
            
            $data{$key} = $val;
        }
        
        return $data;
    }
}
