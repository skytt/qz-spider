<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

//jwxt
Route::get('api/getannouncement','jwxt/PersonalController/getAnnouncement');
Route::get('api/getcourselimit','jwxt/PersonalController/getCourseLimit');
Route::post('api/login','jwxt/PersonalController/doLogin');
Route::get('api/login','jwxt/PersonalController/doLogin');
Route::post('api/getcourse','jwxt/PersonalController/getCourse');
Route::get('api/getcourse','jwxt/PersonalController/getCourse');
Route::post('api/getgrade','jwxt/PersonalController/getGrade');
Route::get('api/getgrade','jwxt/PersonalController/getGrade');
Route::post('api/getemptyroom','jwxt/PersonalController/getEmptyroom');
Route::get('api/getemptyroom','jwxt/PersonalController/getEmptyroom');
Route::post('api/getschoolcalendar','jwxt/PersonalController/getSchoolCalendar');
Route::get('api/getschoolcalendar','jwxt/PersonalController/getSchoolCalendar');
Route::get('api/getdaychange','jwxt/PersonalController/getDayChange');

//library
Route::post('libapi/search','lib/LibraryController/doSearch');
Route::get('libapi/search','lib/LibraryController/doSearch');
Route::post('libapi/getdetails','lib/LibraryController/getDetails');
Route::get('libapi/getdetails','lib/LibraryController/getDetails');
Route::post('libapi/gettop','lib/LibraryController/getHotTop');
Route::get('libapi/gettop','lib/LibraryController/getHotTop');

//gwt
Route::get('gwt/getlist','gwt/gwtController/getNewsList');
Route::get('gwt/getarticle','gwt/gwtController/getNewsArticle');

//Order
Route::get('order/api/getvenuelist','order/OrderController/getVenueList');
Route::get('order/api/getarealist','order/OrderController/getAreaList');
Route::get('order/api/searcharea','order/OrderController/searchArea');
Route::get('order/api/getareadetail','order/OrderController/getAreaDetail');
Route::get('order/api/getuserorder','order/OrderController/getUserOrder');
Route::post('order/api/applyarea','order/OrderController/applyArea');
Route::post('order/api/docompanionaction','order/OrderController/doCompanionAction');
Route::post('order/api/getapplydetail','order/OrderController/getApplyDetail');

//OrderAdmin
Route::get('order/index','order/AdminController/index_view');
Route::get('order/login','order/AdminController/login_view');
Route::get('order/logout','order/AdminController/doLogout');
Route::get('order/list_all_view','order/AdminController/list_all_view');
Route::get('order/list_states1_view','order/AdminController/list_states1_view');
Route::get('order/list_states2_view','order/AdminController/list_states2_view');
Route::get('order/list_states4_view','order/AdminController/list_states4_view');
Route::get('order/area_list_view','order/AdminController/area_list_view');
Route::get('order/area_add_view','order/AdminController/area_add_view');
Route::get('order/area_edit_view','order/AdminController/area_edit_view');
Route::get('order/usermanage_view','order/AdminController/usermanage_view');
Route::get('order/user_add_view','order/AdminController/user_add_view');
Route::get('order/user_edit_view','order/AdminController/user_edit_view');
Route::post('order/login','order/AdminController/doLogin');
Route::post('order/orderpass','order/AdminController/orderPass');
Route::post('order/ordercancel','order/AdminController/orderCancel');
Route::post('order/changeareastate','order/AdminController/changeAreaState');
Route::post('order/addarea','order/AdminController/addArea');
Route::post('order/editarea','order/AdminController/editArea');
Route::post('order/adduser','order/AdminController/addUser');
Route::post('order/edituser','order/AdminController/editUser');
