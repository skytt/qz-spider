const request = require('request');
const iconv = require('iconv-lite');
const config = require('../config/config')

// create a https agent
var https = require('https');
const httpsAgent = new https.Agent({ keepAlive: true });

const timeout = 5000;

const baseHeader = {
  'host': config.request_host,
  'referer': config.request_referer,
  'user-agent': config.request_userAgent,
  'connection': 'keep-alive',
  'agent': httpsAgent,
};

exports.get = (url, params = null, header = {}, isGb2312) => {
  const option = {
    url: config.jwxtBaseUrl + url,
    method: 'GET',
    timeout: timeout,
    encoding: null,
    headers: Object.assign({}, baseHeader, header),
    qs: params
  }
  return baseRequest(option, isGb2312)
}

exports.post = (url, params = {}, header = {}, isGb2312, isJson = false) => {
  const option = {
    url: config.jwxtBaseUrl + url,
    method: 'POST',
    timeout: timeout,
    json: isJson,
    encoding: null,
    headers: Object.assign({}, baseHeader, {
      'content-type': isJson ? 'application/json' : 'application/x-www-form-urlencoded',
    }, header),
    form: params
  }
  return baseRequest(option, isGb2312)
}


// 封装通用 Promise 请求方法
function baseRequest(req_opts, isGb2312 = false) {
  return new Promise((resolve, reject) => {
    request({ forever: true, pool: { maxSockets: 100 }, ...req_opts }, (err, res, data) => {
      if (!err) {
        // 正常返回
        resolve(Object.assign(res, { data: req_opts.json ? data : iconv.decode(data, isGb2312 ? 'gb2312' : 'utf-8').toString() }))
        // resolve({
        //   response: res,
        //   data: data
        // })
      } else {
        // 发生错误
        console.log(err)
        reject(err)
      }
    });

  })
}