<?php

namespace app\jwxt\controller;

use \think\Db;
use app\common\controller\BaseController;

import('Snoopy', EXTEND_PATH, '.class.php');

class PersonalController extends BaseController
{
    private $CI;

    public function _initialize()
    {
        $this->CI = new \Snoopy();
        parent::_initialize();
    }

    public function doLogin()
    {
        if (IS_POST) {
            $username = '';
            $password = '';

            if (input('?post.username')) {
                $username = input('post.username');
            }
            if (input('?post.password')) {
                $password = input('post.password');
            }

            if (!$username || !$password) {
                $data['ret'] = 0;
                $data['msg'] = '用户名或者密码不能为空！';
            } else {
                $login_url = 'http://isea.sztu.edu.cn/jsxsd/xk/LoginToXk';

                $encoded = encodeInp($username) . "%%%" . encodeInp($password); //计算密码

                $login_post_data = [
                    'encoded' => $encoded,
                ];

                $result = $this->jwxt_login($login_url, $login_post_data, true);

                $pattern = '/<div id="Top1_divLoginName" class="Nsb_top_menu_nc" style="color: #000000;">([^<]*?)<\/div\>/';
                $res = preg_match($pattern, $result);
                if (!(bool) $res) {
                    //未登录成功，准备获取系统返回信息
                    $result = mb_convert_encoding($result, "UTF-8", "GBK");
                    //
                    $pattern = '/<font style="display: inline;white-space:nowrap;" color="red">([^<]*?)<\/font\>/';
                    preg_match_all($pattern, $result, $content);
                    $data['ret'] = 0;
                    $data['msg'] = $content[1][0];
                } else {
                    //登录成功，获取姓名和学号
                    preg_match_all($pattern, $result, $content);
                    $data['ret'] = 1;
                    $data['msg'] = $content[1][0];
                }
            }
        } else {
            $data['ret'] = 0;
            $data['msg'] = '请求非法';
        }
        return json($data);
    }

    public function getCourse()
    {
        if (IS_POST) {
            $username = '';
            $password = '';
            $time = '';
            $zc = '';

            if (input('?post.username')) {
                $username = input('post.username');
            }
            if (input('?post.password')) {
                $password = input('post.password');
            }
            if (input('?post.time')) {
                $time = input('post.time');
                $time = ($time == "2017-2018-1") ?  "2011-2012-1" : $time;
            }
            if (input('?post.zc')) {
                $zc = input('post.zc');
            }

            if (!$username || !$password || !$time) {
                $data['ret'] = 0;
                $data['msg'] = '数据不完整！';
            } else {
                $login_url = 'http://isea.sztu.edu.cn/jsxsd/xk/LoginToXk';

                $encoded = encodeInp($username) . "%%%" . encodeInp($password); //计算密码

                $login_post_data = [
                    'encoded' => $encoded,
                ];

                $result = $this->jwxt_login($login_url, $login_post_data, true);

                $pattern = '/<div id="Top1_divLoginName" class="Nsb_top_menu_nc" style="color: #000000;">([^<]*?)<\/div\>/';
                $res = preg_match($pattern, $result);
                if (!(bool) $res) {
                    //未登录成功，准备获取系统返回信息
                    $result = mb_convert_encoding($result, "UTF-8", "GBK");
                    //
                    $pattern = '/<font style="display: inline;white-space:nowrap;" color="red">([^<]*?)<\/font\>/';
                    preg_match_all($pattern, $result, $content);
                    $data['ret'] = 0;
                    $data['msg'] = $content[1][0];
                } else {
                    //登录成功，开始进一步处理
                    if ($zc == "") {
                        $course_post_data = [
                            'xnxq01id' => $time,
                        ];
                    } else {
                        $course_post_data = [
                            'xnxq01id' => $time,
                            'zc' => $zc,
                        ];
                    }
                    $isStudent = (strlen($username) > 8) ? true : false;
                    if ($isStudent) {
                        $course_url = 'http://isea.sztu.edu.cn/jsxsd/xskb/xskb_list.do';
                    } else {
                        $course_url = 'http://isea.sztu.edu.cn/jsxsd/jskb/jskb_list.do';
                    }
                    $content = $this->jwxt_getCourses($course_url, $course_post_data, !$isStudent);
                    if ($content == null) {
                        //获取课表数据为空
                        $data['ret'] = 0;
                        $data['msg'] = "获取课表数据失败";
                    } else {
                        array_shift($content);
                        array_pop($content);
                        $arr = [];
                        foreach ($content as $key => $val) {
                            //array_pop($val);
                            //array_pop($val);
                            foreach ($val as $v) {
                                $arr[$key][] = trim($v);
                            }
                        }
                        $data['ret'] = 1;
                        $data['msg'] = $arr;
                    }
                }
            }
        } else {
            $data['ret'] = 0;
            $data['msg'] = '请求非法';
        }
        return json($data);
    }

    public function getGrade()
    {
        if (IS_POST) {
            $username = '';
            $password = '';
            $time = '';

            if (input('?post.username')) {
                $username = input('post.username');
            }
            if (input('?post.password')) {
                $password = input('post.password');
            }
            if (input('?post.time')) {
                $time = input('post.time');
                //$time = ($time == "2017-2018-1") ?  "2011-2012-1" : $time;
            }

            if (!$username || !$password || !$time) {
                $data['ret'] = 0;
                $data['msg'] = '数据不完整！';
            } else {
                $login_url = 'http://isea.sztu.edu.cn/jsxsd/xk/LoginToXk';

                $encoded = encodeInp($username) . "%%%" . encodeInp($password); //计算密码

                $login_post_data = [
                    'encoded' => $encoded,
                ];

                $result = $this->jwxt_login($login_url, $login_post_data, true);

                $pattern = '/<div id="Top1_divLoginName" class="Nsb_top_menu_nc" style="color: #000000;">([^<]*?)<\/div\>/';
                $res = preg_match($pattern, $result);
                if (!(bool) $res) {
                    //未登录成功，准备获取系统返回信息
                    $result = mb_convert_encoding($result, "UTF-8", "GBK");
                    //
                    $pattern = '/<font style="display: inline;white-space:nowrap;" color="red">([^<]*?)<\/font\>/';
                    preg_match_all($pattern, $result, $content);
                    $data['ret'] = 0;
                    $data['msg'] = $content[1][0];
                } else {
                    //登录成功，开始处理
                    $grade_url = 'http://isea.sztu.edu.cn/jsxsd/kscj/cjcx_list';
                    $content   = $this->jwxt_getGrade($grade_url, $time);
                    if ($content == null) {
                        //获取成绩数据为空
                        $data['ret'] = 0;
                        $data['msg'] = "获取成绩数据失败";
                    } elseif ($content[1][0] == "未查询到数据") {
                        $data['ret'] = 0;
                        $data['msg'] = $content[1][0];
                    } else {
                        $arr = [];
                        array_shift($content);
                        $info = array_pop($content);
                        $all_grade = 0;
                        $all_point = 0;
                        foreach ($content as $k => $v) {
                            $arr[$k][] = trim($v[3]); //课程
                            $arr[$k][] = trim($v[4]); //评级
                            $arr[$k][] = trim($v[6]); //学分
                            $arr[$k][] = trim($v[8]); //绩点
                            $arr[$k][] = trim($v[10]); //课程修读类型
                            $all_grade += trim($v[6]) * trim($v[8]);
                            $all_point += trim($v[6]);
                        }
                        $data['ret'] = 1;
                        $data['msg'] = $arr;
                        $data['msg1'] = $info;
                        $data['avg_point'] = round($all_grade / $all_point, 4);
                    }
                }
            }
        } else {
            $data['ret'] = 0;
            $data['msg'] = '请求非法';
        }
        return json($data);
    }


    public function getEmptyroom()
    {
        if (IS_POST) {
            $username = '';
            $password = '';
            $time = '';
            $week = '';
            $jc = '';
            $zc = '';
            $buildid = '';

            if (input('?post.username')) {
                $username = input('post.username');
            }
            if (input('?post.password')) {
                $password = input('post.password');
            }
            if (input('?post.time')) {
                $time = input('post.time');
                $time = ($time == "2017-2018-1") ?  "2011-2012-1" : $time;
            }
            if (input('?post.buildid')) {
                $buildid = input('post.buildid');
            }
            if (input('?post.week')) {
                $week = input('post.week');
            }
            if (input('?post.jc')) {
                $jc = input('post.jc');
            }
            if (input('?post.zc')) {
                $zc = input('post.zc');
            }

            if (!$username || !$password || !$time || !$week || !$buildid || !$zc) {
                $data['ret'] = 0;
                $data['msg'] = '数据不完整！';
            } else {
                $login_url = 'http://isea.sztu.edu.cn/jsxsd/xk/LoginToXk';

                $encoded = encodeInp($username) . "%%%" . encodeInp($password); //计算密码

                $login_post_data = [
                    'encoded' => $encoded,
                ];

                $result = $this->jwxt_login($login_url, $login_post_data, true);

                $pattern = '/<div id="Top1_divLoginName" class="Nsb_top_menu_nc" style="color: #000000;">([^<]*?)<\/div\>/';
                $res = preg_match($pattern, $result);
                if (!(bool) $res) {
                    //未登录成功，准备获取系统返回信息
                    $result = mb_convert_encoding($result, "UTF-8", "GBK");
                    //
                    $pattern = '/<font style="display: inline;white-space:nowrap;" color="red">([^<]*?)<\/font\>/';
                    preg_match_all($pattern, $result, $content);
                    $data['ret'] = 0;
                    $data['msg'] = $content[1][0];
                } else {
                    //登录成功，开始处理
                    $emptyroom_url = 'http://isea.sztu.edu.cn/jsxsd/kbxx/jsjy_query2';
                    $course_post_data = [
                        'typewhere' => 'jszq',
                        'xnxqh' => $time,
                        'xqid' => 'Vn',
                        'jxlbh' => $buildid,
                        'zc' => $zc,
                        'zc2' => $zc,
                        'xq' => $week,
                        'xq2' => $week,
                        'jc1' => $jc,
                        'jc2' => $jc
                    ];
                    $content = $this->jwxt_getEmptyroom($emptyroom_url, $course_post_data);
                    if (count($content) == 3) {
                        //获取课室数据为空
                        $data['ret'] = 0;
                        $data['msg'] = "获取课室数据失败";
                    } else {
                        array_shift($content);
                        array_pop($content);
                        $data['ret'] = 1;
                        $data['msg'] = $content;
                    }
                }
            }
        } else {
            $data['ret'] = 0;
            $data['msg'] = '请求非法';
        }
        return json($data);
    }

    public function getAnnouncement()
    {
        $data['ret'] = 1;
        $data['msg'] = file_get_contents(ROOT_PATH . 'application/jwxt/data/announcement.txt');
        return json($data);
    }

    public function getCourseLimit()
    {
        $data['ret'] = 1;
        $data['msg'] = file_get_contents(ROOT_PATH . 'application/jwxt/data/courselimit.txt');
        return json($data);
    }

    public function getDayChange()
    {
        $term = '';
        if (input('?get.term')) {
            $term = input('get.term');
        }

        if (!$term) {
            $data['ret'] = 0;
            $data['msg'] = '请求数据不完整';
        } else {
            $databaseobj = Db::table('day_change');
            //$list = $databaseobj->where('term', $term)->where('ori', ">=", date('Y-m-d'))->select();
            $list = $databaseobj->where('term', $term)->select();
            if ($list) {
                $data['ret'] = 1;
                $data['msg'] = '获取成功';
                $res_arr = Array();
                foreach($list as $key => $item){
                    $res_arr[$item['ori']] = $item;
                }
                $data['data'] = $res_arr;
            } else {
                $data['ret'] = 0;
                $data['msg'] = '获取失败';
            }
        }
        return json($data);
    }

    public function getSchoolCalendar()
    {
        if (IS_POST) {
            $time = '';
            if (input('?post.time')) {
                $time = input('post.time');
            }

            if (!$time) {
                $data['ret'] = 0;
                $data['msg'] = '数据不完整！';
            } else {
                $databaseobj = Db::table('school_calendar');
                $list = $databaseobj->where('team', $time)->select();
                $data['ret'] = 1;
                $data['msg'] = '校历获取成功';
                $data['data'] = $list;
            }
        } else {
            $data['ret'] = 0;
            $data['msg'] = '请求非法';
        }
        return json($data);
    }

    protected function jwxt_login($login_url, $login_post_data, $is_check_user = false)
    {
        $this->CI->expandlinks = true;
        $this->CI->host        = 'isea.sztu.edu.cn';
        $this->CI->agent       = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0';
        $this->CI->referer     = 'http://isea.sztu.edu.cn/jsxsd/';
        $this->CI->setcookies();
        $this->CI->submit($login_url, $login_post_data);

        if ($is_check_user) {
            return $this->CI->results;
        }
    }

    protected function jwxt_getCourses($course_url, $post_data, $is_teacher_login = false)
    {
        $this->CI->host        = 'isea.sztu.edu.cn';
        $this->CI->agent       = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0';
        if (!$is_teacher_login) {
            //学生课表
            $this->CI->referer     = 'http://isea.sztu.edu.cn/jsxsd/xskb/xskb_list.do';
        } else {
            //教师课表
            $this->CI->referer     = 'http://isea.sztu.edu.cn/jsxsd/jskb/jskb_list.do';
        }
        $this->CI->submit($course_url, $post_data);
        $content = $this->CI->results;
        if ($content) {
            preg_match_all('/<table id="kbtable"[\w\W]*?>([\w\W]*?)<\/table>/', $content, $out);
            $table = $out[0][0]; //获取整个课表
            return $this->get_td_array($table);
        }
    }

    protected function jwxt_getGrade($grade_url, $time)
    {
        $post_data = [
            'kksj' => $time,
        ];
        $this->CI->host    = 'isea.sztu.edu.cn';
        $this->CI->agent   = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0';
        $this->CI->referer = 'http://isea.sztu.edu.cn/jsxsd/kscj/cjcx_query?Ves632DSdyV=NEW_XSD_XJCJ';
        $this->CI->submit($grade_url, $post_data);
        $content = $this->CI->results;
        if ($content) {
            preg_match_all('/<table id="dataList"[\w\W]*?>([\w\W]*?)<\/table>/', $content, $out);
            $table = $out[0][0]; //获取整个成绩表
            return $this->get_td_array($table);
        }
    }

    protected function jwxt_getEmptyroom($rmptyroom_url, $post_data)
    {
        $this->CI->host        = 'isea.sztu.edu.cn';
        $this->CI->agent       = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0';
        $this->CI->referer     = 'http://isea.sztu.edu.cn/jsxsd/kbxx/jsjy_query';
        $this->CI->submit($rmptyroom_url, $post_data);
        $content = $this->CI->results;
        if ($content) {
            preg_match_all('/<table id="dataList"[\w\W]*?>([\w\W]*?)<\/table>/', $content, $out);
            $table = $out[0][0]; //获取整个课表
            return $this->get_td_array($table, true);
        }
    }

    protected function get_td_array($table, $pop = true)
    {
        //$table = preg_replace("'<div[^>]*?display: none;[^>].*</font><br/></div>'", '', $table);
        $table = preg_replace("'<div[^>]*?class=\"kbcontent1[^>].*</font><br/></div>'", '', $table);
        $table = preg_replace("'<table[^>]*?>'si", '', $table);
        $table = preg_replace("'<input[^>]*?>'si", '', $table);
        $table = preg_replace("'<div[^>]*?>'si", '', $table);
        $table = preg_replace("'th'", 'td', $table);
        $table = preg_replace("'<tr[^>]*?>'si", '', $table);
        $table = preg_replace("'<td[^>]*?>'si", '', $table);
        $table = str_replace('</tr>', '{tr}', $table);
        $table = str_replace('</td>', '{td}', $table);
        $table = preg_replace("'<font title=\'(.*?)\'>'", '|', $table);
        //去掉 HTML 标记
        $table = preg_replace("'<[/!]*?[^<>]*?>'si", '', $table);
        //去掉空白字符
        $table = preg_replace("/[\t\n\r]+/", '', $table);
        $table = preg_replace('/&nbsp;/', '', $table);
        $table = str_replace('[', '|', $table);
        $table = str_replace(']节', '', $table);
        $table = preg_replace("'-----------'", '@@', $table);
        $table = preg_replace("'\'>'", '：', $table);
        $table = preg_replace("'--------(.*?)\|'", '|', $table);
        $table = explode('{tr}', $table);
        if ($pop) {
            array_pop($table);
        }
        $td_array = [];
        foreach ($table as $key => $tr) {
            $td = explode('{td}', $tr);
            if ($pop) {
                array_pop($td);
            }
            $td_array[] = $td;
        }
        return $td_array;
    }
}
