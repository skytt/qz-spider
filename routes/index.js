const router = require('koa-router')();
const account = require('../core/account');

router.get('/', async (ctx, next) => {
  await ctx.render('index', {
    title: 'Hello Koa 2!'
  });
})

router.post('/doLogin', doLogin)
router.post('/getMyInfo', getMyInfo)


// 登录
async function doLogin(ctx) {
  const { username, password } = ctx.request.body
  if (!username || !password) {
    ctx.status = 401;
    ctx.body = {
      msg: '请输入用户名和密码'
    };
    return
  }
  const loginRes = await account.doLogin(username, password);
  const cookies = loginRes.headers['set-cookie'][0];
  switch (loginRes.statusCode) {
    case 302:      // 登录成功，302是因为需要跳转到主界面，此时cookies有效
      ctx.status = 200;
      ctx.body = {
        cookies: cookies,
      };
      break
    case 200:       // 跳转回登录页，证明出现了登录错误，捕获错误类型
      ctx.status = 401;
      const regErrMsg = /<font style="display: inline;white-space:nowrap;" color="red">([^<]*?)<\/font\>/gi;
      ctx.body = {
        msg: regErrMsg.exec(loginRes.data)[1].trim() || '未能成功登录教务系统'
      };
      break
    default:      //  意料意外的返回状态码
      ctx.status = 406;
      ctx.body = {
        msg: '在登录教务系统时发生未知错误'
      };
      break
  }
  ctx.set('Content-Type', 'application/json; charset=utf-8');
}

async function getMyInfo(ctx) {
  const { authorization } = ctx.request.header
  if (!authorization) {
    ctx.status = 401;
    ctx.body = {
      msg: '请携带验证参数'
    };
    return
  }
  const infoRes = await account.getMyInfo(authorization);
  if (infoRes.ret) {
    ctx.status = 200;
    ctx.body = {
      userInfo: infoRes.data
    };
  } else {
    if (infoRes.code === 407) {
      ctx.status = 407;
      ctx.body = {
        msg: 'Token已过期'
      };
    } else {
      ctx.status = 406;
      ctx.body = {
        msg: '未知错误获取失败'
      };
    }
  }
  ctx.set('Content-Type', 'application/json; charset=utf-8');
}

module.exports = router
