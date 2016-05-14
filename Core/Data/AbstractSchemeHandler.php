<?php
namespace Core\Data;

/**
 *
 * @author michael
 *
 */
class AbstractSchemeHandler implements SchemeHandlerInterface
{

    protected $scheme;

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\SchemeHandlerInterface::excecute()
     *
     */
    public function excecute(DataObjectInterface $data)
    {
        if (empty($this->scheme) || empty($thi->scheme['fields'])) {
            return;
        }

        // Let's set some types
        foreach ($data as $name => &$value) {

            // Data does not exist as field in scheme? Skip it!
            if (empty($this->scheme['fields'][$name])) {
                continue;
            }

            // copy field defintion for better reading
            $field = $this->scheme['fields'][$name];

            // Is this field flagged as serialized?
            if (! empty($field['serialize'])) {
                $value = unserialize($value);
            }

            // Empty data value but non empty default value in scheme set? Use it!
            if (empty($value) && ! empty($field['default'])) {
                $value = $field['default'];
            }
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Data\SchemeHandlerInterface::setScheme()
     *
     */
    public function setScheme(array $scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Data\SchemeHandlerInterface::getPrimary()
     */
    public function getPrimary()
    {
        return ! empty($this->scheme['primary']) ? $this->scheme['primary'] : false;
    }
}

