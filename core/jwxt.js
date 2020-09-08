const req = require('./req')
const utils = require('./utils');
require('tls').DEFAULT_MIN_VERSION = 'TLSv1';

// 进行登录并在返回值携带cookies
exports.doLogin = async (username, password) => {
  const url = `xk/LoginToXk`;
  let datas = {
    encoded: utils.encodeInp(username) + '%%%' + utils.encodeInp(password)
  };
  const loginRes = await req.post(url, datas, null, true);

  const cookies = loginRes.headers['set-cookie'][0];
  switch (loginRes.statusCode) {
    case 302:      // 登录成功，302是因为需要跳转到主界面，此时cookies有效
      return {
        ret: true,
        data: cookies
      }
    case 200:       // 跳转回登录页，证明出现了登录错误，捕获错误类型
      const regErrMsg = /<font style="display: inline;white-space:nowrap;" color="red">([^<]*?)<\/font\>/gi;
      return {
        ret: false,
        code: 401,
        msg: regErrMsg.exec(loginRes.data)[1].trim() || '未能成功登录教务系统'
      }
    default:      //  意料意外的返回状态码
      return {
        ret: false,
        code: 406,
        msg: `访问教务系统时发生未知错误`
      }
  }
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
        code: 407,
        msg: 'Token已失效'
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
      code: 406,
      msg: '访问教务系统时发生未知错误'
    };
  }
}