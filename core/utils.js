exports.baseURL = 'https://isea.sztu.edu.cn/jsxsd/';

exports.request_host = 'isea.sztu.edu.cn';
exports.request_referer = 'https://isea.sztu.edu.cn/jsxsd/';
exports.request_userAgent = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0';

// login encode
exports.encodeInp = input => {
  let keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
  let output = "";
  let chr1, chr2, chr3 = "";
  let enc1, enc2, enc3, enc4 = "";
  let i = 0;
  do {
    chr1 = input.charCodeAt(i++);
    chr2 = input.charCodeAt(i++);
    chr3 = input.charCodeAt(i++);
    enc1 = chr1 >> 2;
    enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
    enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
    enc4 = chr3 & 63;
    if (isNaN(chr2)) {
      enc3 = enc4 = 64;
    } else if (isNaN(chr3)) {
      enc4 = 64;
    }
    output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2) + keyStr.charAt(enc3) + keyStr.charAt(enc4);
    chr1 = chr2 = chr3 = "";
    enc1 = enc2 = enc3 = enc4 = "";
  } while (i < input.length)
  return output;
}

// 检查 session 是否过期
exports.isSessionExpired = response => {
  try {
    const setCookies = response.headers['set-cookie'][0];
    if (setCookies.indexOf('JSESSIONID') !== -1) {
      return true;
    }
    return false;
  } catch (e) {
    return false;
  }
}

exports.tdToArray = (context, isCourse = false, doubleTd = false) => {
  if (doubleTd) {
    // 成绩界面会出现两个</td>粘在一起的情况，优先处理成一个
    context = context.replace(/<\/td><\/td>/sig, '<\/td>');
  }
  if (isCourse) {
    // 先进行单课程重复显示的内容剔除
    context = context.replace(RegExp(/<div[^>]*?class=\"kbcontent1[^>].*<\/font><br\/><\/div>/si, 'g'), '');
  }

  context = context.replace(/<table[^>]*?>/sig, '');          // 去除<table>标签
  context = context.replace(/<input[^>]*?>/sig, '');          // 去除<input>标签
  context = context.replace(/<div[^>]*?>/sig, '');            // 去除<div>标签
  context = context.replace(/<span[^>]*?>/sig, '');           // 去除<div>标签
  context = context.replace(/<th/sig, '<td');                 // 将<th>转为<td>
  context = context.replace(/<\/th/sig, '</td');              // 将</th>转为</td>
  context = context.replace(/<tr[^>]*?>/sig, '');             // 去除<tr>
  context = context.replace(/<td[^>]*?>/sig, '');             // 去除<td>
  context = context.replace(/<\/tr>/sig, '{tr}');             // 对</tr>标签特殊标记
  context = context.replace(/<\/td>/sig, '{td}');             // 对</td>标签特殊标记

  if (isCourse) {
    // 处理将font标签（作为title特殊处理）
    context = context.replace(/<font[^>]*?title='([^>]*?)'[^>]*?>/sig, '{||}$1{|}');
    context = context.replace(/<\/font([^>]*?)>/sig, '{||}');
    // 处理课程节数标签
    context = context.replace(/\[([\d+-]*)\]节/sig, '{||}节次{|}$1');
    // 处理多个课程分割横线
    context = context.replace(/-----------/sig, '{@@}');
    // 处理无用横线
    context = context.replace(/--------/sig, '');
  }
  context = context.replace(/<[/!]*?[^<>]*?>/sig, '');        // 去除HTML标记
  context = context.replace(/[\t\n\r]+/sig, '');              // 去掉空白字符
  context = context.replace(/&nbsp;/sig, '');                 // 去除html空格
  context = context.replace(/\{\|\|\}\{\|\|\}/sig, '{||}');   // 去除重复的标签

  const resultArr = context.split('{tr}');
  // 删除尾部空行
  if (!resultArr[resultArr.length - 1].trim()) {
    resultArr.pop();
  }
  resultArr.forEach((row, index) => {
    resultArr[index] = row.split('{td}');
    resultArr[index].pop()    // split以后最后会多出一项，去掉
    resultArr[index].forEach((cell, celli) => {
      resultArr[index][celli] = resultArr[index][celli].trim()
    })
  });

  return resultArr;
}