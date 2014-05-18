<?php

namespace Entities\Template\ViewElements;

use Entities\Object;
use Entities\Template\AbstractModel;
use Entities\Template\AbstractView;
use \App;

class FormBuilder
{
    protected $_boundView = null;
    protected $_optionsList = array();

    /**
     * Bound ro parent view object
     *
     * @param AbstractView $view
     * @return $this
     */
    public function bindView(AbstractView $view)
    {
        $this->_boundView = $view;
        return $this;
    }

    /**
     * Return URL to handle required action
     *
     * @param  string $type   Action type
     * @param  AbstractModel  $model Item
     *
     * @return string
     */
    public function getActionUrl($type, AbstractModel $model)
    {
        $formHandler = App::instance()->getService('config')->getConfigArea('system/from_handler');
        $params = array(
            'entity' => $model->getEntityName(),
            $model->getEntityPrimaryKey() => $model->getEntityId()
        );
        return $this->getBoundView()->getBaseUrl($formHandler . $type, $params);
    }

    /**
     * Return element name related to current entity
     *
     * @param  string $element
     * @param  string $name Input name
     * @return string
     */
    public function getElementName($name, $element = null)
    {
        $inputName = $this->getBoundView()->getEntityName() . '[' . $name . ']';
        if ($element == 'multiple') {
            $inputName .= '[]';
        }
        return $inputName;
    }

    /**
     * Return parent view object
     *
     * @return \Entities\Template\AbstractView
     */
    public function getBoundView()
    {
        if (!$this->_boundView) {
            App::error('Not specified parent view for formBuilder object');
        }
        return $this->_boundView;
    }


    public function getRelativeElementHtml($elData = array(), $item, $relatedEntityName)
    {
        $relatedEntity = App::instance()->getService('factory')->getTemplateOf(ucfirst($relatedEntityName));

        $data = new Object();
        $data->setData($elData);
        $data->setElementName($this->getElementName($elData['element_name'], $data->getElementType()));
        $data->setOptions($this->prepareOptions($elData['element_name'] ,$item, $relatedEntity));

        $html = '<select id="' . $data->getElementId() . '" multiple="'. $data->getElementType() .'" name=" '. $data->getElementName() . '">';
        foreach ($data->getOptions() as $option) {
            $html .= '<option ' . $this->_getSelectedText($option) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function prepareOptions($elementName, AbstractModel $item, AbstractModel $relatedEntity)
    {
        if (!isset($this->_optionsList[$elementName])) {
            $this->_optionsList[$elementName] = $relatedEntity->getCollection()->toOptionArray();
        }
        $options = $this->_optionsList[$elementName];
        if($selectedItems = $item->getData($elementName)) {
            if (!is_array($selectedItems)) {
                $selectedItems = array($selectedItems);
            }
            foreach ($selectedItems as $id) {
                foreach ($options as &$option) {
                    if ($option['value'] == $id) {
                        $option['selected'] = true;
                    }
                }
            }
        }
        return $options;
    }

    protected function _getSelectedText(array $option)
    {
        if (!empty($option['selected'])) {
            return 'selected';
        }
        return '';
    }
}