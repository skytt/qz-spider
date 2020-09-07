<?php
namespace app\common\controller;
use think\Controller;

//
// Created by zhanxiaoping 
// zhanxp@me.com
//
class BaseController extends Controller {
    protected $url;
	protected $request;
	protected $module;
	protected $controller;
	protected $action;

    public function _initialize() {
		//获取request信息
		$this->requestInfo();
    }
    
    //request信息
	protected function requestInfo() {
		$this->param = $this->request->param();
		defined('MODULE_NAME') or define('MODULE_NAME', $this->request->module());
		defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $this->request->controller());
		defined('ACTION_NAME') or define('ACTION_NAME', $this->request->action());
		defined('IS_POST') or define('IS_POST', $this->request->isPost());
		defined('IS_GET') or define('IS_GET', $this->request->isGet());
		$this->url = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
		$this->assign('request', $this->request);
		$this->assign('param', $this->param);
	}
}