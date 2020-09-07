<?php

namespace app\gwt\controller;

use think\Cache;
use app\common\controller\BaseController;

import('Snoopy', EXTEND_PATH, '.class.php');

class GwtController extends BaseController
{
    private $CI;

    public function _initialize()
    {
        $this->CI = new \Snoopy();
        parent::_initialize();
    }

    public function getNewsList()
    {
        $page = input('param.page');
        $type = input('param.type');
        //$this->gwt_login(true); //先登录
        //$list_url = 'http://r.tytion.net:81/WebUsers/NewsReadList.aspx?hid_Page='.(($page==null)?'1':$page).'&typeSubclass='.(($type==null)?'0':$type);
        $list_url = 'http://cdn.r.tytion.net/WebUsers/NewsReadList.aspx?hid_Page=' . (($page == null) ? '1' : $page) . '&typeSubclass=' . (($type == null) ? '0' : $type);
        $content = $this->getContent($list_url);
        if ($content) {
            preg_match_all('/<table id="table_Count"[\w\W]*?>([\w\W]*?)<\/table>/', $content, $out);
            $table = $out[0][0]; //获取整个表
            $table = str_replace('<!--', '', $table);
            $table = str_replace('-->', '', $table);
            $table = str_replace("src='images/mail.png' />", '>1', $table);
            $table = str_replace("a href='NewsDetail.aspx?newsId=", '>', $table);
            $table = str_replace("' target='_blank' style='c", '</td><td><', $table);
            $table_arr = $this->get_td_array($table, true);
            array_shift($table_arr);
            $res_arr = array();
            foreach ($table_arr as $key => $item) {
                $obj_item = [
                    'id' => $item[0],
                    'type' => $item[1],
                    'author' => $item[2],
                    'newsid' => $item[3],
                    'title' => $item[4],
                    'attachment' => $item[5] ? 1 : 0,
                    'post_time' => $item[6],
                    'top' => ($item[7] == '未置顶') ? 0 : 1,
                ];
                array_push($res_arr, $obj_item);
            }

            $data['ret'] = 1;
            $data['msg'] = '获取成功';
            $data['data'] = $res_arr;
        } else {
            $data['ret'] = 0;
            $data['msg'] = '获取失败';
        }
        return json($data);
    }

    public function search()
    {
        $page = input('param.page');
        $keyword = input('param.keyword');
        $search_url = 'http://cdn.r.tytion.net/WebUsers/NewsReadList.aspx?typeSubclass=0';
        $login_post_data = [
            '__VIEWSTATE' => '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',
            '__VIEWSTATEGENERATOR' => '61F1A20E',
            '__EVENTVALIDATION' => '/wEdAAnfNrrhmfkK+vXTw1E9CxBJJl2Aq/0kbNSBlFpf7E3x2iIoua7bmMRzcPX3lJP2U13SuQcSL6aQDg9Xs0L9/OBOSLETwEpIPSGV/A3bSuaAq0y5MVIlEQvOPYRn2EO/B/8nMoUx5eo7yLuWCem+6kfZApaNSElw3v3JDwUc71xXJ/iPO/g278Fv2JUEQ/fArqGLQCBMpO5i7Ok0KqOsrU7IdBlh3FcRHcorKs/Ru5NduA==',
            'txt_startTime' => date("Y-m-d", strtotime("-1 years", strtotime(date("Y-m-d")))),
            'txt_endTime' => date("Y-m-d"),
            'txt_Key' => $keyword,
            'hid_Page' => ($page == null) ? '1' : $page,
        ];
        $content = $this->getContentPost($search_url, $login_post_data);
        //return $content;
        if ($content) {
            if (strstr($content, 'Error') !== false) {
                //error
                $data['ret'] = 0;
                $data['msg'] = '参数错误，获取失败';
            } else {
                $count['result'] = $this->get_textbetween($content, '<input type="hidden" name="hid_Count" id="hid_Count" value="', "\" />\r\n        \r\n        <table id=\"table_Count");
                if ($count['result'] == 0) {
                    $data['ret'] = 0;
                    $data['msg'] = '关键字“' . $keyword . '”没有搜索结果';
                } else {
                    $count['page'] = $this->get_textbetween($content, 'id="hid_PageCount" value="', "\" />\r\n        <input type=\"hidden\" name=\"hid_Count\"");
                    preg_match_all('/<table id="table_Count"[\w\W]*?>([\w\W]*?)<\/table>/', $content, $out);
                    $table = $out[0][0]; //获取整个表
                    $table = str_replace('<!--', '', $table);
                    $table = str_replace('-->', '', $table);
                    $table = str_replace("src='images/mail.png' />", '>1', $table);
                    $table = str_replace("a href='NewsDetail.aspx?newsId=", '>', $table);
                    $table = str_replace("' target='_blank' style='c", '</td><td><', $table);
                    $table_arr = $this->get_td_array($table, true);
                    array_shift($table_arr);
                    $res_arr = array();
                    foreach ($table_arr as $key => $item) {
                        $obj_item = [
                            'id' => $item[0],
                            'type' => $item[1],
                            'author' => $item[2],
                            'newsid' => $item[3],
                            'title' => $item[4],
                            'attachment' => $item[5] ? 1 : 0,
                            'post_time' => $item[6],
                            'top' => ($item[7] == '未置顶') ? 0 : 1,
                        ];
                        array_push($res_arr, $obj_item);
                    }


                    $data['ret'] = 1;
                    $data['msg'] = '获取成功';
                    $data['count'] = $count;
                    $data['data'] = $res_arr;
                }
            }
        } else {
            $data['ret'] = 0;
            $data['msg'] = '获取失败';
        }
        return json($data);
    }

    public function getNewsArticle()
    {
        $newsid = input('param.newsid');
        if ($newsid) {
            //$this->gwt_login(true);//先登录
            //$article_url = 'http://r.tytion.net:81/WebUsers/NewsDetail.aspx?newsId='.$newsid;
            $article_url = 'http://cdn.r.tytion.net/WebUsers/NewsDetail.aspx?newsId=' . $newsid;
            $content = $this->getContent($article_url);
            if ($content) {
                preg_match_all('/<div class="content"[\w\W]*?>([\w\W]*?)<div class="bottom_nav">/', $content, $out);
                $article_html = $out[0][0]; //获取整个表
                $article_html = str_replace('<div class="content">', '', $article_html);
                $article_html = str_replace('<div class="bottom_nav">', '', $article_html);

                //remove remark
                $article_html = preg_replace("'<!--[^>]*?-->'si", '', $article_html);
                $article_html = preg_replace("'<\?[^>]*?>'si", '', $article_html);
                //remove span
                $article_html = preg_replace("'<span[^>]*?>'si", '', $article_html);
                $article_html = str_replace('</span>', '', $article_html);
                //remove font
                $article_html = preg_replace("'<font[^>]*?>'si", '', $article_html);
                $article_html = str_replace('</font>', '', $article_html);
                //remove p
                $article_html = preg_replace("'<p[^>]*?align=\"right\"[^>]*?>'si", '<toright>', $article_html);
                $article_html = preg_replace("'<p style=\"[^>]*?TEXT-ALIGN:[^>]*?right[^>]*?>'si", '<toright>', $article_html);
                $article_html = preg_replace("'<p[^>]*?>'si", '<p>', $article_html);
                $article_html = str_replace('<toright>', '<p style="text-align: right;">', $article_html);
                //remove td/tr
                $tj = '/<(TBODY|THEAD|TFOOT|TH|TR|TD)[^>]*?( colspan="\d")*((?!colspan)(?!rowspan).)*?( rowspan="\d")*((?!colspan)(?!rowspan).)*?( colspan="\d")*((?!colspan)(?!rowspan).)*?>/i';
                $article_html_tmp = preg_replace($tj, "<$1 $2 $3$4>", $article_html);
                $article_html  = $article_html_tmp ? $article_html_tmp : $article_html;
                $article_html_tmp = preg_replace('/<table.*?>/', '<table width="auto" style="border-spacing: 0;border-collapse: collapse;">', $article_html);
                $article_html  = $article_html_tmp ? $article_html_tmp : $article_html;
                $article_html = str_replace('<td', '<td style="border:solid black 1pt;"', $article_html);

                /*
                $tj = '/<(TBODY|THEAD|TFOOT|TH|TR|TD)[^>]*?(ColSpan\s*=\s*["\']?[^"\'>\s]*["\']?[^>]*?)?(RowSpan\s*=\s*["\']?[^"\'>\s]*["\']?[^>]*?)?>/i';
                $article_html = preg_replace($tj, "<$1 $2 $3>", $article_html);
                */

                //remove o:p
                $article_html = preg_replace("'<o:p[^>]*?>'si", '', $article_html);
                $article_html = str_replace('</o:p>', '', $article_html);
                //remove div
                $article_html = preg_replace("'<div class=\"(.*?)\">'", '|', $article_html);
                $article_html = str_replace('</div>', '', $article_html);
                //remove r/n
                $article_html = str_replace("\r\n", '', $article_html);
                //deal footer
                $article_html = str_replace("<div style='width:100%;'>", '|', $article_html);
                //deal image  
                //$article_html = preg_replace("'<img([^>]*?)src=\"'si", "$0http://r.tytion.net:81", $article_html);
                $article_html = preg_replace("'<img([^>]*?)src=\"'si", "$0http://cdn.r.tytion.net", $article_html);
                //$article_html = str_replace('<img src="', '<img src="http://r.tytion.net:81', $article_html);

                $article_arr = explode('|', $article_html);
                foreach ($article_arr as $key => $tr) {
                    $article_arr[$key] = trim($tr);
                }

                //return $article_html;
                //var_dump($article_arr);
                //attachment
                $is_attachment = (sizeof($article_arr) == 6) ? true : false;


                $authorandposttime =  explode('&nbsp;&nbsp;', $article_arr[2]);
                $updateandclick_text = $is_attachment ? $article_arr[5] : $article_arr[4];
                $updateandclick_text = str_replace('(更新于', '', $updateandclick_text);
                $updateandclick_text = str_replace(')', '', $updateandclick_text);
                $updateandclick =  explode('&nbsp;&nbsp;&nbsp;&nbsp;点击数:', $updateandclick_text);
                $attachment_arr = array();
                if ($is_attachment) {
                    $attachment_html = $article_arr[4];
                    preg_match_all('/<div style=\'font-size:12px; width:100%;\'>([\w\W]*?)<\/a>/', $attachment_html, $out_attachment);
                    $attachment_arr_tmp = $out_attachment[0]; //获取整个表
                    foreach ($attachment_arr_tmp as $key => $tr) {
                        $attachment_obj = [
                            'link' => $this->get_textbetween($tr, "附件：<a href='", "' target="),
                            'name' => $this->get_textbetween($tr, '_blank">', "</a>"),
                        ];
                        array_push($attachment_arr, $attachment_obj);
                    }
                }
                $article_obj = [
                    'title' => $article_arr[1],
                    'author' => trim($authorandposttime[0]),
                    'post_time' => trim($authorandposttime[1]),
                    'update_time' => trim($updateandclick[0]),
                    'attachment' => $is_attachment ? $attachment_arr : null,
                    'content' => $article_arr[3],
                    'click' => trim($updateandclick[1]),
                ];

                $data['ret'] = 1;
                $data['msg'] = '获取成功';
                $data['data'] = $article_obj;
                //$data['data_ori'] = $article_arr; 
            } else {
                $data['ret'] = 0;
                $data['msg'] = '获取失败';
            }
        } else {
            $data['ret'] = 0;
            $data['msg'] = '没有Newsid！';
        }
        return json($data);
    }



    protected function gwt_login($is_check_user = false)
    {
        //$url = 'http://r.tytion.net:81/WebUsers/Index.aspx';
        $url = 'http://cdn.r.tytion.net/WebUsers/Index.aspx';
        $login_post_data = [
            '__EVENTTARGET' => '',
            '__EVENTARGUMENT' => '',
            '__LASTFOCUS' => '',
            '__VIEWSTATE' => '/wEPDwUJOTA0NjIwODg1D2QWAgIDD2QWBgIBDxAPFgYeDkRhdGFWYWx1ZUZpZWxkBQZVc2VySWQeDURhdGFUZXh0RmllbGQFDU9yZGVyVXNlck5hbWUeC18hRGF0YUJvdW5kZ2QQFSkADOmYheiniOeUqOaItwsxLumYruWPjOeQmwgyLuW+kOWImgszLuW8oOWyqem4vxE0LuWFmuaUv+WKnuWFrOWupCY1LuWFmuWnlOe7hOe7h+mDqO+8iOWFmuWnlOe7n+aImOmDqO+8iQg2LuW3peS8mhE3LuWFmuWnlOWuo+S8oOmDqBE4Lue6quajgOebkeWvn+WupAs5LuagoeWbouWnlBIxMC7lrqHorqHms5XliqHlrqQMMTEu5pWZ5Yqh6YOoGzEyLuenkeeglOS4juagoeS8geWQiOS9nOmDqCExMy7lm73pmYXlkIjkvZzkuI7lrabnlJ/lt6XkvZzpg6ghMTQu5oiY55Wl6KeE5YiS5LiO5Y+R5bGV5Yqe5YWs5a6kEjE1LuS6uuWKm+i1hOa6kOmDqBIxNi7orqHliJLotKLliqHpg6gMMTcu5Z+65bu66YOoEjE4LuWQjuWLpOS/nemanOmDqAwxOS7moKHljLvpmaIMMjAu5Zu+5Lmm6aaGDzIxLuS/oeaBr+S4reW/gxsyMi7kuK3lvrfmmbrog73liLbpgKDlrabpmaIeMjMu5aSn5pWw5o2u5LiO5LqS6IGU572R5a2m6ZmiHjI0LuaWsOadkOaWmeS4juaWsOiDvea6kOWtpumZoh4yNS7ln47luILkuqTpgJrkuI7nianmtYHlrabpmaIeMjYu5YGl5bq35LiO546v5aKD5bel56iL5a2m6ZmiFTI3LuWIm+aEj+iuvuiuoeWtpumZohUyOC7lm73pmYXkuqTmtYHlrabpmaIYMjku6LSo6YeP5ZKM5qCH5YeG5a2m6ZmiMDMwLuS6uuaWh+ekvuenkeWtpumZou+8iOmprOWFi+aAneS4u+S5ieWtpumZou+8iQ8zMS7noJTnqbbnlJ/pmaIPMzIu5L2T6IKy5a2m6ZmiJzMzLuWFiOi/m+adkOaWmea1i+ivleaKgOacr+eglOeptuS4reW/gxUzNC7lronlhajkv53ljavkuK3lv4MkMzUu5Zu95pyJ6LWE5Lqn5LiO5a6e6aqM5a6k566h55CG6YOoJDM2LuWIm+S4muWIm+WuouS4juWwseS4muaMh+WvvOS4reW/gyEzNy7ph4fotK3kuI7mi5vmipXmoIfnrqHnkIbkuK3lv4MhMzgu5qCh5Zut5L+h5oGv5YyW5bel5L2c5Yqe5YWs5a6kCTEwMC5hZG1pbhUpACQ5YjJmMWQ4ZS05YjI5LTQ5ZDMtOWYwZS0xNGQ5MDFhNGFkZjgkYzQxMTQzZmMtNWU2My00NjkxLWE0YjUtMmNmN2M5MWY0M2Q4JDE3NzE2ZmFiLWUzY2YtNDBhZC05MWVkLWRhOTYyOTAwNzEyOCRhNjk5YjNmMi04MDViLTQ4OWItYmRkMC1hYjU2ZGMwMmFmMjQkYTMxZWNmY2QtZjU4Yi00NTZlLThjZTYtYzZjMzY0YThkODI4JDU0OWUxMzM0LTkxM2MtNDI1My04MmY5LWU1MGI5YmJiMTlmMSQwMWJiNTc4OS01ZDI0LTQyNmItYjEzMy1iOGVkZjFhNTZiZGMkZTI3MDI0YjItYmJkZC00YTliLTkwMTAtM2Q5YTVlOTg2OWU4JDQyZWYwNGNiLTY5Y2MtNDNhMi1iZjJlLTI1ODhkODAzNmUyYyRmZDhhNzhjZi1hNGU1LTRlZDgtYWUzMS1hZDc3ZWEyZWRjMGIkMmIxNDU4M2MtMWM4NC00MmMyLWJjYzktM2I3YzNhNWIzNzQ1JDczNTIyMGYwLTgxMmMtNGU1YS05MTk3LThkNDc1MDI2NWU2YiRlNGVjMGQ3MC1mMTRhLTQ5YTItYTIyZS1jYjJmNTYxMzA1NDEkNmRiNmYyMGYtMjljZC00NzRiLWI4NzEtYjUyOTdmMTNkYTQ5JDJlOTI4YmMxLWRiNjAtNDgwZi04ZGM3LWVhZDA5MTRhN2U3NiRhMGY1N2Y2NC01OTAwLTRjNjEtYjc3Yi0xNTJjYjk2Mzk2NGQkN2Q2NTk5MjgtMDJlMy00MjBjLThkM2UtYTdmZjc4NDBlM2JlJDgwMDBlZjE5LWY0MGEtNDg2MC04ZmI1LTgxYmRkM2VkMGRmNiQ3ZWRkNzEwNS00NWY5LTQ4ZmMtOWE3ZS1lNzM3ZWUxZGQ5ODckZDBjNDIyZDUtZGQxMC00NzYxLTk2MTAtNWE2ZmI4NDg2MTc0JDVkMTZhMTJjLTEzNWEtNGYxMy04OTVlLTg5NzY0ZGJkN2FkNCRiN2Y4NjA3YS1mNTU2LTQ0ZGMtYTQxZi01MDg0MzFkM2U3MGMkMDRlM2QwMzctNDdmNy00NTg0LTgzYzUtMzU5YTc3YWZkMDljJDY0NTQ5ZDY4LTZhMGItNGVkZS04Njc1LTY3MGU3OGViNjA3MyQxMjRlZWIxNy03OTcyLTQxYWEtYTg5OC0yMGUxZDBmMTY4NmQkYjM0NTFiZDEtNTBlOS00ZDEzLWIyNWEtYWUyNWYwYTEyYTZlJDRiMDUwMDg2LTM1YzctNDU0Yi1hZTY3LWIwY2UzN2RhYzk0NCQ3NzkwZGMzZS1mOTYyLTQzMWMtOGVkYy05ZTczODZmMGIyNzEkODllMTI0ODEtODU0Mi00MmVkLTk2MDgtZjA1MDhjZDM1NDIxJGJkNmM0NjcxLTlmMjItNDIwZi05ODNiLTNmYWE2MGVhYzhiYSQyNWQwZWVmMS0zY2MzLTQyZWQtOGMyZS0wMTVjMWRjOTkyODAkZDk1NmE4ZTgtY2I1Ny00M2VmLWEzYjUtODZkZDFkZWE2NzM1JDYxNmIzYTM2LTg2ZTctNDI3YS05MTJkLWE5Zjk3OGFkYmRlNCQ5YmZiMDA5MS1jNzkzLTRkZjUtYjNjMS02ZDg1NTA3OTI3MzEkOTFiOTc3MzUtZDQ2YS00NTM1LWEyMGUtYmI1ZWI1OTRmZTBmJGIxZWU1MGM3LTk0NGMtNDRjNC1iNDc3LTAyZWVmZTUxOTZlYyQ1OTBmMjcwOS0xZTRmLTQyYjEtYmM4NS1kNzlhMWMxNzkyMjckODgxNzNlYmQtNzIwMi00NzVlLTkzYzAtNzQ3MjhmMzlhZGE3JGJhNTZhMWZiLTI1ZTEtNGU4ZC05OWIyLTkyOGIxMDQzY2Y1YSQwNWQ0MWU2Zi01Y2FiLTRjOTYtYjJhZi1lOTM0NzJhNzA5NmQUKwMpZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2dnZ2cWAQIBZAIDDw9kFgIeBXZhbHVlBQYxMjM0NTZkAgkPFgIeBFRleHQF8gw8bGk+IDxiPuagoeWbrTwvYj4gPGEgaHJlZj0namF2YXNjcmlwdDpyZXR1cm4gZmFsc2U7JyBvbkNsaWNrPSJTdWIoJy9XZWJVc2Vycy9OZXdzRGV0YWlsLmFzcHg/bmV3c0lkPTQ1MTFjNjBkLWVjY2MtNDMzNS05MmFjLTAwZTQzNmZlNDI2OScpIj5RUemYheivu+KAouacuuaehOeJiOivleeUqOmAmuefpe+8iOWNs+aXpei1ty0yMDIwLjEuNO+8iTwvYT4gPHNwYW4+MjAxOS8wOS8wNjwvc3Bhbj4gPC9saT48bGk+IDxiPuaVmeWtpjwvYj4gPGEgaHJlZj0namF2YXNjcmlwdDpyZXR1cm4gZmFsc2U7JyBvbkNsaWNrPSJTdWIoJy9XZWJVc2Vycy9OZXdzRGV0YWlsLmFzcHg/bmV3c0lkPTJmMDNhYWE4LTg4YzEtNGZhNC05MDFhLWNiNDc3MTFjMzRkOScpIj7lhbPkuo7lgZrlpb0yMDIw5bm056GV5aOr56CU56m255Sf5o6o5YWN55Sf5oub55Sf5a6j5Lyg5bel5L2c5pyJ5YWz5LqL6aG555qE6YCa55+lPC9hPiA8c3Bhbj4yMDE5LzA5LzA2PC9zcGFuPiA8L2xpPjxsaT4gPGI+6KGM5pS/PC9iPiA8YSBocmVmPSdqYXZhc2NyaXB0OnJldHVybiBmYWxzZTsnIG9uQ2xpY2s9IlN1YignL1dlYlVzZXJzL05ld3NEZXRhaWwuYXNweD9uZXdzSWQ9ZTI0YmUwZTItMTdmZi00MmUyLWE0OTctMjNmZGJhYmEwMDdlJykiPuWFs+S6juS4vuWKnuKAnOaIkeWSjOaIkeeahOelluWbveKAneW6huelneS4reWNjuS6uuawkeWFseWSjOWbveaIkOerizcw5ZGo5bm05b6B5paH5q+U6LWb55qE6YCa55+lPC9hPiA8c3Bhbj4yMDE5LzA5LzA2PC9zcGFuPiA8L2xpPjxsaT4gPGI+5a2m55SfPC9iPiA8YSBocmVmPSdqYXZhc2NyaXB0OnJldHVybiBmYWxzZTsnIG9uQ2xpY2s9IlN1YignL1dlYlVzZXJzL05ld3NEZXRhaWwuYXNweD9uZXdzSWQ9OWJmMTg1YWUtMzFjOC00ZmYzLThjZTItYzBkYWExYjk0MzdiJykiPuWFs+S6juW8gOWxleagoeWGheWLpOW3peWKqeWtpuWyl+S9jeeUs+ivt+W3peS9nOeahOmAmuefpTwvYT4gPHNwYW4+MjAxOS8wOS8wNjwvc3Bhbj4gPC9saT48bGk+IDxiPuihjOaUvzwvYj4gPGEgaHJlZj0namF2YXNjcmlwdDpyZXR1cm4gZmFsc2U7JyBvbkNsaWNrPSJTdWIoJy9XZWJVc2Vycy9OZXdzRGV0YWlsLmFzcHg/bmV3c0lkPTQ3MDFkN2UyLThiYjctNGZjMi1hYzMyLTk5MTY0NGJlYjY2OCcpIj7lhbPkuo7lhazlhbHmlZnlrabmpbzkuLTml7bovabovobnrqHmjqfnmoTpgJrnn6U8L2E+IDxzcGFuPjIwMTkvMDkvMDY8L3NwYW4+IDwvbGk+PGxpPiA8Yj7np5HnoJQ8L2I+IDxhIGhyZWY9J2phdmFzY3JpcHQ6cmV0dXJuIGZhbHNlOycgb25DbGljaz0iU3ViKCcvV2ViVXNlcnMvTmV3c0RldGFpbC5hc3B4P25ld3NJZD05ZmM1ZjRkZC02NjMwLTRiNTQtOTJjNS1lMDZiYjE3MmQ0OTUnKSI+5YWz5LqO6aKG5Y+W56eR56CU6aG555uu57uP6LS55Y2h55qE6YCa55+lPC9hPiA8c3Bhbj4yMDE5LzA5LzA2PC9zcGFuPiA8L2xpPjxsaT4gPGI+6KGM5pS/PC9iPiA8YSBocmVmPSdqYXZhc2NyaXB0OnJldHVybiBmYWxzZTsnIG9uQ2xpY2s9IlN1YignL1dlYlVzZXJzL05ld3NEZXRhaWwuYXNweD9uZXdzSWQ9NWVlZDAyNDYtMzExYS00MjU4LTkzZmItZGU1NjBlZGQ3NDhkJykiPuWFs+S6juWPrOW8gOaWsOWtpuacn+W3peS9nOS8muiurueahOmAmuefpTwvYT4gPHNwYW4+MjAxOS8wOS8wNjwvc3Bhbj4gPC9saT5kZKD/9zc2wb7F/Z/jkBIOiZIg9NSE/pWADMw87No0wQtn',
            '__VIEWSTATEGENERATOR' => '4AFB8FE2',
            '__EVENTVALIDATION' => '/wEdAC5HY7IaV97WboVjeMxqtqvZjuelBbJ3VmM/JopQSg2xVDDKL1veOPzrZ4ju8wS1h9PymS9LBbY7ty3LlF3O9UcuIP+qw6Ma3dfSUzwt7wcZIMu078xx/m1gxY451sQl/l2Cn25mfC/KM4QOVSMVHWr7br2w6GjKV633ujykVP4cyMDjvjemGC2nQW/uD3OVvB9L/ua/2rQd3j4pgwGer/2D5/nD2tPjd62TBKMg5ZuzcwZqvrrQWJSNxSUzYImhofb7V34lXMNtFVRZbgX7Z+6LDScOJOGcdWthX/rslNkqApOm/9PRC35iD+rV1xvs0Eev87RWv4rRr9ApU9/qaiHrW1gnUEf5VnBwDIX6OIYz7Y+f7EamxmEsCIlZTNzeKTeLENeQ0fzPrsNTQuuRVV8SzRuXv/avtoZHPs5UMrfF6OI0AcRUaaTtEsGacSoljKnR9H/w2ZnXsDA8Uk+/BojfUJsvZhezkBJRrZI9hD6SSSZxyZZ7RQEPj9oRKSQBganJCR+P8Qi/mq0+0yeHRHEanyk6Yt6RwUuD4UXc8r07m7fy6dva1B8cQ+cfK+lFPPULXHaEefndkzGH/UkKlWaUFeMxwdPP/9Wf8eLef3heaqwR4TA8lt+XViUeZghBN67YcKMQdytJekr+OlY/2GZmUrZgx4RGwOPw6BwBJKrzaZIlisWsmHL9bhzFYRMolmWGEL7fZ0ySCbcLkRyC8kucuyEiGKyh8N0bHjbmJKsLANhHjTEuC35+9rUZXiIEjJoe5GkoVrOE4juPJDCUPoB9UB0axdCir2SfsE4mDzjJ2yajCd/uYAbEXroRwNazszwUyuec4M9mWtOR5aWDrd5aG4vk+7CvuljQKcKnIIfXHV6bs9S4RUZR0MKsFXjGgxCZ/AQchReuWjeXJQY8BznFDKqcz2/OTqXC3fnYhq+hN3DVWoSEvRCzmIlX0qynpXpGNKWXrMx0QSAXHf3DUjE6uSMqhCZ8TKqiqBSLwvULGj2F+UIltFlRqRln7A3oGPc=',
            'ddl_User' => '9b2f1d8e-9b29-49d3-9f0e-14d901a4adf8',
            'txt_Password' => '123456',
            'hid_Password' => '123456',
            'btn_Login' => '登录',
            'hid_Url' => '',
        ];
        $this->CI->cookies["ASP.NET_SessionId"] = 'go4lpmxqifp4wypzoushxux1';
        //$this->CI->agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.132 Safari/537.36';
        $this->CI->setcookies();
        $this->CI->submit($url, $login_post_data);
        if ($is_check_user) {
            return $this->CI->results;
        }
    }

    protected function getContent($url)
    {
        $this->CI->cookies["ASP.NET_SessionId"] = 'go4lpmxqifp4wypzoushxux1';
        $this->CI->fetch($url);
        $content = $this->CI->results;

        $res = strpos($content, "深圳技术大学");
        if ($res !== false) {
            return $content;
        } else {
            $this->gwt_login(true);
            //$this->CI->cookies["ASP.NET_SessionId"] = 'go4lpmxqifp4wypzoushxux1';
            $this->CI->fetch($url);
            return $this->CI->results;
        }
    }

    protected function getContentPost($url, $post_data)
    {
        $this->CI->cookies["ASP.NET_SessionId"] = 'go4lpmxqifp4wypzoushxux1';
        $this->CI->submit($url, $post_data);
        $content = $this->CI->results;

        $res = strpos($content, "深圳技术大学");
        if ($res !== false) {
            return $content;
        } else {
            $this->gwt_login(true);
            //$this->CI->cookies["ASP.NET_SessionId"] = 'go4lpmxqifp4wypzoushxux1';
            $this->CI->submit($url, $post_data);
            return $this->CI->results;
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
        $table = preg_replace("'-----------'", '@', $table);
        $table = preg_replace("'\'>'", '：', $table);
        $table = preg_replace("'--------(.*?)\|'", '|', $table);
        $table = explode('{tr}', $table);
        if ($pop) {
            array_pop($table);
        }
        $td_array = [];
        foreach ($table as $key => $tr) {
            $td = explode('{td}', $tr);
            foreach ($td as $key => $tr) {
                $td[$key] = trim($tr);
            }
            if ($pop) {
                array_pop($td);
            }
            $td_array[] = $td;
        }
        return $td_array;
    }

    protected function get_textbetween($input, $start, $end)
    {
        $substr = substr($input, strlen($start) + strpos($input, $start), (strlen($input) - strpos($input, $end)) * (-1));
        return $substr;
    }
}
