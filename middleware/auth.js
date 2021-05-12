const resModel = require('../config/resModel');

exports.checkAuthorization = async (ctx, next) => {
  const { header: { authorization } } = ctx.request;
  if (!authorization) {
    ctx.throw(resModel.CODE.NO_AUTH, resModel.TEXT.CARRY_COOKIE_REQ)
    return;
  }
  ctx.state.cookie = authorization
  return await next();
}