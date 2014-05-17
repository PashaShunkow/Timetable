<?php

namespace Entities\Handler;

use Entities\Template\AbstractView;

use \App;

class View extends AbstractView
{
    protected $_factory;

    public function __construct()
    {
        $this->_factory = App::instance()->getService('factory');
    }

    public function saveAction()
    {
        $getParams  = $this->getRequest()->getGET();
        $postParams = $this->getRequest()->getPOST();
        if(!empty($getParams['entity']))
        {
            $entityName = $getParams['entity'];
            $model = $this->_getFactory()->createModel($entityName);
            if (isset($postParams[$model->getEntityName()])) {
                $data = $postParams[$model->getEntityName()];
                if (isset($getParams[$model->getEntityPrimaryKey()])) {
                    $data[$model->getEntityPrimaryKey()] = $getParams[$model->getEntityPrimaryKey()];
                }
                $model->setData($data);
                $model->save();
                $this->_redirectOnPrevious(array($model->getEntityPrimaryKey() => $model->getEntityId()));
                return;
            }else{
                App::error('Handler: Cant save model: ' . $entityName . ' $data array is empty');
            }
        }else{
            App::error('Handler: Cant save model, entity name is not passed');
        }
    }

    public function editAction()
    {


    }

    public function deleteAction()
    {
        $getParams = $this->getRequest()->getGET();
        if (!empty($getParams['entity'])) {
            $entityName = $getParams['entity'];
            $model = $this->_getFactory()->createModel($entityName);
            if (isset($getParams[$model->getEntityPrimaryKey()]) && $model->load($getParams[$model->getEntityPrimaryKey()])->delete()) {
                $this->_redirectOnPrevious();
                return;
            } else {
                App::error('Handler: Cant save model: ' . $entityName . ' id param is empty or there is no item with same id in data base');
            }
        } else {
            App::error('Handler: Cant delete model, entity name is not passed');
        }
    }

    public function getHtml()
    {
        return 'Handler';
    }

    /**
     * Return system factory object
     *
     * @return \System\Factory
     */
    protected function _getFactory()
    {
        return $this->_factory;
    }

    /**
     * Redirect on previous page
     *
     * @param null $params Get params
     */
    protected function _redirectOnPrevious($params = null)
    {
        App::redirectOn($this->getRequest()->getSERVER('HTTP_REFERER'), $params, true);
    }
}