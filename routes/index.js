const router = require('koa-router')();

router.get('/', async (ctx, next) => {
  ctx.body = `Welcome to iSZTU`
})

module.exports = router