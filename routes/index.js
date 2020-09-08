const router = require('koa-router')();
const jwxt = require('../core/jwxt');

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
  const loginRes = await jwxt.doLogin(username, password);
  if (loginRes.ret) {
    ctx.status = 200;
    ctx.body = {
      cookies: loginRes.data,
    };
  } else {
    ctx.status = loginRes.code;
    ctx.body = {
      msg: loginRes.msg
    };
  }
  ctx.set('Content-Type', 'application/json; charset=utf-8');
}

// 获取个人信息
async function getMyInfo(ctx) {
  const { authorization } = ctx.request.header
  if (!authorization) {
    ctx.status = 401;
    ctx.body = {
      msg: '请携带验证参数'
    };
    return
  }
  const infoRes = await jwxt.getMyInfo(authorization);
  if (infoRes.ret) {
    ctx.status = 200;
    ctx.body = {
      userInfo: infoRes.data
    };
  } else {
    ctx.status = infoRes.code;
    ctx.body = {
      msg: infoRes.msg
    };
  }
  ctx.set('Content-Type', 'application/json; charset=utf-8');
}

module.exports = router
