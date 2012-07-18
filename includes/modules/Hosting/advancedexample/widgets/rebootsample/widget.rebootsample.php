<?php

class widget_rebootsample extends HostingWidget {

    protected $description = 'This is description of widget that will be displayed in adminarea';
    protected $widgetfullname = 'Widget function name';

    /**
     * HostBill will call this function when widget is visited from clientarea
     * @param HostingModule $module Your provisioning module object
     * @return array
     */
    public function clientFunction(&$module) {


        return array('mytemplate.tpl', array('variable1' => 'Value 1', 'variable2' => 'Wow, isnt it simple? ;'));
    }

}