const resModal = require('../config/resModal');

exports.checkAuthorization = async (ctx, next) => {
  const { header: { authorization } } = ctx.request;
  if (!authorization) {
    ctx.status = resModal.CODE.NO_AUTH;
    ctx.body = {
      msg: resModal.TEXT.CARRY_COOKIE_REQ
    };
    ctx.set('Content-Type', 'application/json; charset=utf-8');
    // ctx.throw(resModal.CODE.NO_AUTH, resModal.TEXT.CARRY_COOKIE_REQ)
    return false;
  }
  return await next();
}