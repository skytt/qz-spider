const router = require('koa-router')();

router.get('/', async (ctx, next) => {
  await ctx.render('index', {
    title: 'iSZTU'
  });
})

router.get('/base/termList', async (ctx, next) => {
  ctx.status = 200;
  ctx.body = {
    termlist: [
      { name: '2020-2021-1', from: '1599321600000', to: '1611331200000', beginGrade: 2020 },
      { name: '2019-2020-3', from: '1595088000000', to: '1599235200000', beginGrade: 2019 },
      { name: '2019-2020-2', from: '1582992000000', to: '1595001600000', beginGrade: 2019 },
      { name: '2019-2020-1', from: '1567267200000', to: '1581696000000', beginGrade: 2019 },
      { name: '2018-2019-3', from: '1563033600000', to: '1567180800000', beginGrade: 2018 },
      { name: '2018-2019-2', from: '1550937600000', to: '1562947200000', beginGrade: 2018 },
      { name: '2018-2019-1', from: '1535817600000', to: '1550851200000', beginGrade: 2018 },
      { name: '2017-2018-3', from: '1533398400000', to: '1535731200000', beginGrade: 2017 },
      { name: '2017-2018-2', from: '1520092800000', to: '1531497600000', beginGrade: 2017 },
      { name: '2017-2018-1', from: '1504368000000', to: '1520006400000', beginGrade: 2017 },
    ]
  };
  ctx.set('Content-Type', 'application/json; charset=utf-8');
})

router.get('/base/announcement', async (ctx, next) => {
  ctx.status = 200;
  ctx.body = {
    data: '欢迎使用iSZTU！'
  };
  ctx.set('Content-Type', 'application/json; charset=utf-8');
})

module.exports = router