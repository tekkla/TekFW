<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Content\Html\Form\Select;
use Core\Lib\Amvc\App;

/**
 * DataSelect.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DataSelect extends Select
{

    /**
     * The data from which the options of the select will be created
     *
     * @var array
     */
    protected $datasource;

    /**
     * How to use the data in the selects option
     *
     * @var string
     */
    protected $datatype;

    /**
     * The value which should causes an option to be selected.
     * Can be a value or an array of values
     *
     * @var mixed
     */
    protected $selected;

    /**
     * Sets a datasource.
     *
     * @param App $app Name of app the model is of
     * @param string $model Name of model
     * @param string $func Action to run on model
     * @param string $params Array of parameter used by the model
     * @param string $datatype How to use the modeldata in the select options (value and inner value)
     *
     * @return \Core\Lib\Content\Html\Controls\DataSelect
     */
    public function setDataSource(App $app, $model, $func, array $params = [], $datatype = 'assoc')
    {
        // Create model object
        $model = $app->getModel($model);

        // Get data from model and use is as datasource
        $this->datasource = $this->di->invokeMethod($model, $func, $params);

        // Set the dataype
        $this->datatype = $datatype;

        return $this;
    }

    /**
     * Set one or more values to set as selected
     *
     * @param int|string|array
     *
     * @return \Core\Lib\Content\Html\Controls\DataSelect
     */
    public function setSelectedValue($selected)
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * Builds and returna html code
     *
     * @see \Core\Lib\Content\Html\Form\Select::build()
     */
    public function build()
    {
        foreach ($this->datasource as $row) {

            $option = $this->createOption();

            // inner will always be used
            $option->setInner($row[1]);

            // if we have an assoc datasource we use the value attribute
            if ($this->datatype == 'assoc') {
                $option->setValue($row[0]);
            }

            // in dependence of the data type is value to be selected $val or $inner
            if (isset($this->selected)) {
                // A list of selected?
                if (is_array($this->selected)) {
                    if (array_search(($this->datatype == 'assoc' ? $row[0] : $row[1]), $this->selected)) {
                        $option->isSelected(1);
                    }
                } // Or a value to look for?
                else {
                    if ($this->selected == ($this->datatype == 'assoc' ? $row[0] : $row[1])) {
                        $option->isSelected(1);
                    }
                }
            }
        }

        return parent::build();
    }
}
