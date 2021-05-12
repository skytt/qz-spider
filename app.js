const Koa = require('koa')
const app = new Koa()
const json = require('koa-json')
const bodyparser = require('koa-bodyparser')

// error message format
app.use(async (ctx, next) => {
  try {
    await next()
  } catch (err) {
    ctx.response.status = err.statusCode || err.status || 500;
    ctx.response.body = {
      msg: err.message
    };
    // 手动释放error事件
    ctx.app.emit('error', err, ctx);
  }
});

// middlewares
app.use(bodyparser({
  enableTypes: ['json', 'form', 'text']
}))
app.use(json())
app.use(require('koa-static')(__dirname + '/public'))

// logger
app.use(async (ctx, next) => {
  const start = new Date()
  await next()
  const ms = new Date() - start
  console.log(`[${ctx.status}]${ctx.method} ${ctx.url} - ${ms}ms`)
})

// routes
const indexRouter = require('./routes/index')
const baseRouter = require('./routes/base')
const jwxtRouter = require('./routes/jwxt')
app.use(indexRouter.routes(), indexRouter.allowedMethods())
app.use(baseRouter.routes(), baseRouter.allowedMethods())
app.use(jwxtRouter.routes(), jwxtRouter.allowedMethods())

// error-recoder
app.on('error', (err, ctx) => {
  console.error('server error', err, ctx)
});

module.exports = app
