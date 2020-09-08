exports.baseURL = 'https://isea.sztu.edu.cn/jsxsd/';

exports.request_host = 'isea.sztu.edu.cn';
exports.request_referer = 'https://isea.sztu.edu.cn/jsxsd/';
exports.request_userAgent = 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:43.0) Gecko/20100101 Firefox/43.0'

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
      enc3 = enc4 = 64
    } else if (isNaN(chr3)) {
      enc4 = 64
    }
    output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2) + keyStr.charAt(enc3) + keyStr.charAt(enc4);
    chr1 = chr2 = chr3 = "";
    enc1 = enc2 = enc3 = enc4 = ""
  } while (i < input.length);
  return output
}

// 检查 session 是否过期
exports.isSessionExpired = response => {
  try {
    const setCookies = response.headers['set-cookie'][0]
    if (setCookies.indexOf('JSESSIONID') !== -1) {
      return true
    }
    return false
  } catch (e) {
    return false
  }
}