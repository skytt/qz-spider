const router = require('koa-router')();

const resModal = require('../config/resModal');
const authMid = require('../middleware/auth')

const jwxt = require('../core/jwxt');
const checkAuthorization = authMid.checkAuthorization;

router.prefix('/jwxt');

router.post('/doLogin', doLogin);
router.post('/getMyInfo', checkAuthorization, getMyInfo);
router.post('/getCourses', checkAuthorization, getCourses);
router.post('/getGrade', checkAuthorization, getGrade);
router.post('/getEmptyRoom', checkAuthorization, getEmptyRoom);

// 登录
async function doLogin(ctx) {
  const { body: { username, password } } = ctx.request
  if (!username || !password) {
    ctx.status = resModal.CODE.PARAMS_NOT_ENOUGH;
    ctx.body = {
      msg: resModal.TEXT.ENTER_NAME_AND_PWD
    };
    return false;
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
  if (!term) {
    ctx.status = resModal.CODE.PARAMS_NOT_ENOUGH;
    ctx.body = {
      msg: resModal.TEXT.ENTER_QUREY_TERM
    };
    return false;
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
  if (!term) {
    ctx.status = resModal.CODE.PARAMS_NOT_ENOUGH;
    ctx.body = {
      msg: resModal.TEXT.ENTER_QUREY_TERM
    };
    return false;
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

// 获取空教室
async function getEmptyRoom(ctx) {
  const { header: { authorization }, body: { term, buildid, week, day, session } } = ctx.request
  if (!term || !buildid || !week || !day) {
    ctx.status = resModal.CODE.PARAMS_NOT_ENOUGH;
    ctx.body = {
      msg: resModal.TEXT.PARAMS_NOT_ENOUGH
    };
    return false;
  }
  const roomRes = await jwxt.getEmptyRoom(authorization, term, buildid, week, day, session);
  if (roomRes.ret) {
    ctx.status = resModal.CODE.OK;
    ctx.body = {
      sessionTitle: roomRes.sessionTitle,
      roomInfo: roomRes.roomInfo
    };
  } else {
    ctx.status = roomRes.code;
    ctx.body = {
      msg: roomRes.msg
    };
  }
  ctx.set('Content-Type', 'application/json; charset=utf-8');
}

module.exports = router