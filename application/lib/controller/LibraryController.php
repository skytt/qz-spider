<?php
namespace app\lib\controller;

use app\common\controller\BaseController;
import('Snoopy', EXTEND_PATH,'.class.php');

class LibraryController extends BaseController
{
	private $CI;
	
	public function _initialize(){
		$this->CI= new \Snoopy();
        parent::_initialize();
    }

    public function doSearch()
    {
        if (IS_POST) {
            $keyword = "";
            $page = "";
            if (input('?post.keyword')) {
                $keyword = input('post.keyword');
            }
            if (input('?post.page')) {
                $page = input('post.page');
            }

            if (!$keyword ) {
				$data['ret'] = 0;
				$data['msg'] = '数据不完整！';
            }else{
                //开始处理
                if ($page == 1 || !$page){
                    $search_url = 'http://oleopac.lib.sztu.edu.cn/searchresult.aspx?dt=ALL&cl=ALL&dp=10&sf=M_PUB_YEAR&ob=DESC&sm=table&dept=ALL';
                    $content = $this->library_search($search_url, $keyword, 1);
                }else{
                    $keyword_coded = urlencode(iconv('utf-8', 'gb2312', urlencode(iconv('utf-8', 'gb2312', $keyword))));
                    $search_url = "http://oleopac.lib.sztu.edu.cn/showpageforlucenesearchAjax.aspx?anywords%3D".$keyword_coded."%26dt%3DALL%26cl%3DALL%26dp%3D10%26sf%3DM_PUB_YEAR%26ob%3DDESC%26sm%3Dtable%26dept%3DALL%26page%3D".$page."=&_=";
                    $content = $this->library_getDetailsAfterFirstPage($search_url);
                }
                
                if (strpos($content, "<table") === false)
                {
                    //未识别到结果列表
                    $message = $this->getSubstr($content, '<div class="msg">', '<br />');
                    $data['ret'] = 0;
                    $data['msg'] = "获取图书数据失败";
                    $data['data'] = trim($message);
                }else{
                    preg_match_all('/<table[\w\W]*? class="tb">([\w\W]*?)<\/table>/', $content, $out);
                    $table = $out[0][0]; //获取整个表
                    $tablearray = $this->get_td_array($table, true, 'search');
                    $arr = [];
                    foreach ($tablearray as $key => $val) {
                        array_pop($val);
                        foreach ($val as $v) {
                            $arr[$key][] = trim($v);
                        }
                    }
                    $data['ret'] = 1;
                    $data['msg'] = '查询成功';
                    if($page == 1 || !$page){
                        $arr_number['result'] = $this->getSubstr($content, 'ContentPlaceHolder1_countlbl" style="color:Red;">', '</span>');
                        $arr_number['page'] = $this->getSubstr($content, '<span id="ContentPlaceHolder1_gplblfl2">', '</span>页');
                        $data['number'] = $arr_number;
                    }
                    $data['title'] = array_shift($arr);
                    $data['data'] = $arr;
                }
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '请求非法';
        }
        return json($data);
    }


    public function getDetails()
    {
        if (IS_POST) {
            $bookid = "";
            if (input('?post.bookid')) {
                $bookid = input('post.bookid');
            }

            if (!$bookid ) {
				$data['ret'] = 0;
				$data['msg'] = '数据不完整！';
            }else{
                //开始处理
                $emptyroom_url = 'http://oleopac.lib.sztu.edu.cn/bookinfo.aspx?ctrlno=';
                $content = $this->library_getDetails($emptyroom_url, $bookid);
                if (strpos($content, "系统控制号") === false)
                {
                    //未获取信息
                    $message = $this->getSubstr($content, '<div id="searchnotfound">', '</div>');
                    $data['ret'] = 0;
                    $data['msg'] = "获取图书数据失败";
                    $data['data'] = trim($message);
                }else{
                    //图书信息部分
                    preg_match_all('/<div id="carddiv">([\w\W]*?)<span[\w\W]*? id="ContentPlaceHolder1_bookcardinfolbl">([\w\W]*?)<\/span>/', $content, $out);
                    $info_word = $out[0][0]; //获取整个表
                    $info_word = str_replace("　", '', $info_word);
                    $info_arr = explode('<br/><br/>',$info_word);
                    $info_arrout = [];
                    foreach ($info_arr as $val) {
                        if ($val != ""){
                            $info_arrevery = explode('<br/>',$val);
                            $tmparr = [];
                            foreach ($info_arrevery as $v) {
                                if ($v != "")
                                {
                                    $tmpwd = preg_replace("'<[/!]*?[^<>]*?>'si", '', $v);
                                    array_push($tmparr,trim($tmpwd));
                                }
                            }
                            array_push($info_arrout,$tmparr);
                        }
                    }
                    //馆藏信息部分
                    preg_match_all('/<table[\w\W]*? class="tb">([\w\W]*?)<\/table>/', $content, $out);
                    $collection_table = $out[0][0]; //获取整个表
                    $collection_tablearray = $this->get_td_array($collection_table, true);
                    $collection_arr = [];
                    foreach ($collection_tablearray as $key => $val) {
                        foreach ($val as $v) {
                            $collection_arr[$key][] = trim($v);
                        }
                    }
                    $collection_arrout['title'] = array_shift($collection_arr);
                    $collection_arrout['data'] = $collection_arr;

                    //封装最终返回数据
                    $data['ret'] = 1;
                    $data['msg'] = '数据获取成功';
                    $data['data'] = $info_arrout;
                    $data['collection'] = $collection_arrout;
                }
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '请求非法';
        }
        return json($data);
    }

    public function getHotTop($number = 10)
    {
        if (IS_POST) {
            //开始处理
            if (input('?post.number')) {
                $number = input('post.number');
            }
            $top_url = 'http://oleopac.lib.sztu.edu.cn/top100.aspx?sparaname=anywords';
            $content = $this->library_getHotTop($top_url);
            if (!$content)
            {
                //未获取信息
                $data['ret'] = 0;
                $data['msg'] = "获取排行数据失败";
            }else{
                if($number > 100)
                {
                    $data['ret'] = 0;
                    $data['msg'] = '请求搜索词个数超限';
                    }else{
                    //排行信息获取成功，开始处理
                    preg_match_all('/<a onclick=\'createLoadingDiv[\w\W]*?sm=table&dept=ALL">([\w\W]*?)<\/a>([\w\W]*?)<\/td>/', $content, $out);
                    $toparr = $out[0]; //获取整个表
                    $arrout = [];
                    for ($i=0; $i<$number; $i++)
                    {
                        $tmpwd = trim(preg_replace("'<[/!]*?[^<>]*?>'si", '', $toparr[$i]));
                        $tmparr['name'] = substr($tmpwd,0,strrpos($tmpwd,"("));
                        $tmparr['count'] = intval($this->getSubstr($tmpwd,'(',')'));
                        array_push($arrout,$tmparr);
                    }
                    $data['ret'] = 1;
                    $data['msg'] = '数据获取成功';
                    $data['data'] = $arrout;
                }
            }
        }else{
            $data['ret'] = 0;
            $data['msg'] = '请求非法';
        }
        return json($data);
    }





    protected function library_search($search_url, $keyword, $page = 1)
    {
        $keyword = urlencode(iconv('utf-8', 'gb2312', $keyword));
        return $this->curl_get($search_url.'&anywords='.$keyword.'&page='.$page);
      	$this->CI->host        = 'oleopac.lib.szu.edu.cn';
        $this->CI->agent       = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0';
        $this->CI->referer     = 'http://oleopac.lib.sztu.edu.cn/search.aspx';
        $this->CI->setcookies();
        $this->CI->fetch($search_url.'&anywords='.$keyword.'&page='.$page);
        return $this->CI->results;
    }

    protected function library_getDetails($details_url, $bookid)
    {
        return $this->curl_get($details_url.$bookid);
      	$this->CI->host        = 'oleopac.lib.szu.edu.cn';
        $this->CI->agent       = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0';
        $this->CI->referer     = 'http://oleopac.lib.sztu.edu.cn/search.aspx';
        $this->CI->setcookies();
        $this->CI->fetch($details_url.$bookid);
        return $this->CI->results;
    }

    protected function library_getHotTop($details_url)
    {
        return $this->curl_get($details_url);
      	$this->CI->host        = 'oleopac.lib.szu.edu.cn';
        $this->CI->agent       = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0';
        $this->CI->referer     = 'http://oleopac.lib.sztu.edu.cn/search.aspx';
        $this->CI->setcookies();
        $this->CI->fetch($details_url);
        return $this->CI->results;
    }

    protected function library_getDetailsAfterFirstPage($details_url)
    {
        return $this->curl_get($details_url);
      	$this->CI->host        = 'oleopac.lib.szu.edu.cn';
        $this->CI->agent       = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0';
        $this->CI->referer     = 'http://oleopac.lib.sztu.edu.cn/searchresult.aspx';
        $this->CI->setcookies();
        $this->CI->fetch($details_url);
        return $this->CI->results;
    }

    protected function getSubstr($str, $leftStr, $rightStr)
    {
        $left = strpos($str, $leftStr);
        $right = strpos($str, $rightStr,$left);
        if($left < 0 or $right < $left) return '';
        return substr($str, $left + strlen($leftStr), $right-$left-strlen($leftStr));
    }

    protected function curl_get($url, $referer = "http://oleopac.lib.sztu.edu.cn/search.aspx"){
        $ch = curl_init(); 
        curl_setopt ($ch, CURLOPT_URL, $url); 
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0');
        return curl_exec($ch);
    }
    
    protected function get_td_array($table,$pop = true, $part = null)
    {
        if($part == "search")
        {//搜索部分处理
            $table = preg_replace("'\" target=\"_blank\">'", '</td><td>', $table);
            $table = preg_replace("'<a href=\"bookinfo.aspx\?ctrlno='", '', $table);
            $table = preg_replace("'<td [\w\W]*?>序号'", '<td>序号</td><td>id', $table);
        }
        
        //常规表格处理
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
        if($pop)
        {
            array_pop($table);
        }
        $td_array = [];
        foreach ($table as $key=>$tr) {
            $td = explode('{td}', $tr);
            if($pop)
            {
                array_pop($td);
            }
            $td_array[] = $td;
        }
        return $td_array;
    }

}
