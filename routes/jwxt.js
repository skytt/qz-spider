const router = require('koa-router')();

const resModel = require('../config/resModel');
const authMid = require('../middleware/auth')

const jwxtFunc = require('../core/jwxt');

// 定义前缀
router.prefix('/jwxt');

// 登录
router.post('/doLogin', async (ctx, next) => {
  const { body: { username, password } } = ctx.request
  if (!username || !password) {
    ctx.throw(resModel.CODE.PARAMS_NOT_ENOUGH, resModel.TEXT.ENTER_NAME_AND_PWD)
    return
  }
  const loginRes = await jwxtFunc.doLogin(username, password);
  if (loginRes.ret) {
    ctx.body = {
      cookies: loginRes.data,
    };
  } else {
    ctx.throw(loginRes.code, loginRes.msg)
  }
});

// 为后续方法开启cookie验证
router.use(authMid.checkAuthorization)

// 获取个人信息
router.post('/getMyInfo', async (ctx, next) => {
  const { cookie } = ctx.state
  const infoRes = await jwxtFunc.getMyInfo(cookie);
  if (infoRes.ret) {
    ctx.body = {
      userInfo: infoRes.data
    };
  } else {
    ctx.throw(infoRes.code, infoRes.msg)
  }
});

// 获取课程信息
router.post('/getCourses', async (ctx, next) => {
  const { state: { cookie }, request: { body: { term, zc } } } = ctx
  if (!term) {
    ctx.throw(resModel.CODE.PARAMS_NOT_ENOUGH, resModel.TEXT.ENTER_QUREY_TERM)
    return;
  }
  const courseRes = await jwxtFunc.getCourses(cookie, term, zc);
  if (courseRes.ret) {
    ctx.body = {
      courses: courseRes.courses,
      remark: courseRes.remark
    };
  } else {
    ctx.throw(courseRes.code, courseRes.msg)
  }
});

// 获取成绩列表
router.post('/getGrade', async (ctx, next) => {
  const { state: { cookie }, request: { body: { term } } } = ctx
  if (!term) {
    ctx.throw(resModel.CODE.PARAMS_NOT_ENOUGH, resModel.TEXT.ENTER_QUREY_TERM)
    return
  }
  const gradeRes = await jwxtFunc.getGrade(cookie, term);
  if (gradeRes.ret) {
    ctx.status = resModel.CODE.OK;
    ctx.body = {
      count: gradeRes.count,
      grade: gradeRes.grade
    };
  } else {
    ctx.throw(gradeRes.code, gradeRes.msg)
  }
});

// 获取空教室
router.post('/getEmptyRoom', async (ctx, next) => {
  const { state: { cookie }, request: { body: { term, buildid, week, day, session } } } = ctx
  if (!term || !buildid || !week || !day) {
    ctx.throw(resModel.CODE.PARAMS_NOT_ENOUGH, resModel.TEXT.PARAMS_NOT_ENOUGH)
    return
  }
  const roomRes = await jwxtFunc.getEmptyRoom(cookie, term, buildid, week, day, session);
  if (roomRes.ret) {
    ctx.status = resModel.CODE.OK;
    ctx.body = {
      sessionTitle: roomRes.sessionTitle,
      roomInfo: roomRes.roomInfo
    };
  } else {
    ctx.throw(roomRes.code, roomRes.msg)
  }
});


module.exports = router