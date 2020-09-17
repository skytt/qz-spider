const router = require('koa-router')();

router.get('/', async (ctx, next) => {
  await ctx.render('index', {
    title: 'iSZTU'
  });
})

router.get('/base/termList', async (ctx, next) => {
  ctx.status = 200;
  ctx.body = {
    data: [
      '2020-2021-1',
      '2019-2020-3',
      '2019-2020-2',
      '2019-2020-1',
    ]
  };
  ctx.set('Content-Type', 'application/json; charset=utf-8');
})

router.get('/base/announcement', async (ctx, next) => {
  ctx.status = 200;
  ctx.body = {
    data: '欢迎使用iSZTU'
  };
  ctx.set('Content-Type', 'application/json; charset=utf-8');
})

module.exports = router