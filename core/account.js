const req = require('./req')
const utils = require('./utils');
require('tls').DEFAULT_MIN_VERSION = 'TLSv1';

// 进行登录并在返回值携带cookies
exports.doLogin = (username, password) => {
  const url = `xk/LoginToXk`;
  let datas = {
    encoded: utils.encodeInp(username) + '%%%' + utils.encodeInp(password)
  };
  return req.post(url, datas, null, true);
}

// 通过cookies获取个人资料
exports.getMyInfo = async (cookies) => {
  const url = `framework/xsMain.jsp`;
  const customHeader = { 'cookie': cookies };

  try {
    const myInfo = await req.get(url, null, customHeader);
    if (utils.isSessionExpired(myInfo)) {
      return {
        ret: false,
        msg: 'Token已失效',
        code: 407
      };
    }
    const regDiv = /<div id="Top1_divLoginName" class="Nsb_top_menu_nc" style="color: #000000;">([^<]*?)<\/div\>/gi;
    const nameAndNum = regDiv.exec(myInfo.data)[1].trim().split('(')
    return {
      ret: true,
      data: {
        name: nameAndNum[0],
        number: nameAndNum[1].substr(0, nameAndNum[1].length - 1)
      }
    };
  } catch (e) {
    return {
      ret: false,
      msg: '获取个人资料失败'
    };
  }
}