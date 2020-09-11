const router = require('koa-router')();
const jwxt = require('../core/jwxt');
const resModal = require('../core/resModal')

router.get('/', async (ctx, next) => {
  await ctx.render('index', {
    title: 'Hello Koa 2!'
  });
})

router.post('/doLogin', doLogin)
router.post('/getMyInfo', getMyInfo)
router.post('/getCourses', getCourses)
router.post('/getGrade', getGrade)

// 登录
async function doLogin(ctx) {
  const { body: { username, password } } = ctx.request
  if (!username || !password) {
    ctx.status = resModal.CODE.PARAMS_NOT_ENOUGH;
    ctx.body = {
      msg: resModal.TEXT.ENTER_NAME_AND_PWD
    };
    return
  }
  const loginRes = await jwxt.doLogin(username, password);
  if (loginRes.ret) {
    ctx.status = resModal.CODE.OK;
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
  const { header: { authorization } } = ctx.request
  if (!authorization) {
    ctx.status = resModal.CODE.NO_AUTH;
    ctx.body = {
      msg: resModal.CODE.CARRY_COOKIE_REQ
    };
    return
  }
  const infoRes = await jwxt.getMyInfo(authorization);
  if (infoRes.ret) {
    ctx.status = resModal.CODE.OK;
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

// 获取课程信息
async function getCourses(ctx) {
  const { header: { authorization }, body: { term, zc } } = ctx.request
  if (!authorization) {
    ctx.status = resModal.CODE.NO_AUTH;
    ctx.body = {
      msg: resModal.TEXT.CARRY_COOKIE_REQ
    };
    return;
  }
  if (!term) {
    ctx.status = resModal.CODE.PARAMS_NOT_ENOUGH;
    ctx.body = {
      msg: resModal.TEXT.ENTER_QUREY_TERM
    };
    return;
  }
  const courseRes = await jwxt.getCourses(authorization, term, zc);
  if (courseRes.ret) {
    ctx.status = resModal.CODE.OK;
    ctx.body = {
      courses: courseRes.courses,
      remark: courseRes.remark
    };
  } else {
    ctx.status = courseRes.code;
    ctx.body = {
      msg: courseRes.msg
    };
  }
  ctx.set('Content-Type', 'application/json; charset=utf-8');
}

// 获取成绩列表
async function getGrade(ctx) {
  const { header: { authorization }, body: { term } } = ctx.request
  if (!authorization) {
    ctx.status = resModal.CODE.NO_AUTH;
    ctx.body = {
      msg: resModal.TEXT.CARRY_COOKIE_REQ
    };
    return;
  }
  if (!term) {
    ctx.status = resModal.CODE.PARAMS_NOT_ENOUGH;
    ctx.body = {
      msg: resModal.TEXT.ENTER_QUREY_TERM
    };
    return;
  }
  const gradeRes = await jwxt.getGrade(authorization, term);
  if (gradeRes.ret) {
    ctx.status = resModal.CODE.OK;
    ctx.body = {
      count: gradeRes.count,
      grade: gradeRes.grade
    };
  } else {
    ctx.status = gradeRes.code;
    ctx.body = {
      msg: gradeRes.msg
    };
  }
  ctx.set('Content-Type', 'application/json; charset=utf-8');
}

module.exports = router
