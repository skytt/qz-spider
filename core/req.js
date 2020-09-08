const request = require('request');
const iconv = require('iconv-lite');
const utils = require('./utils');

var https = require('https');
const httpsAgent = new https.Agent({ keepAlive: true });

const timeout = 5000;
const baseHeader = {
  'host': utils.request_host,
  'user-agent': utils.request_userAgent,
  'connection': 'keep-alive',
  'agent': httpsAgent,
};

exports.get = (url, params = null, header = {}, isGb2312) => {
  const option = {
    url: utils.baseURL + url,
    method: 'GET',
    timeout: timeout,  // 设置请求超时，单位是毫秒
    encoding: null,
    headers: Object.assign({}, baseHeader, header),
    qs: params    // 进行GET请求时，此处的参数一定是qs,请注意，如果是POST请求，参数是form
  }
  return baseRequest(option, isGb2312)
}

exports.post = (url, params = {}, header = {}, isGb2312, isJson = false) => {
  const option = {
    url: utils.baseURL + url,
    method: 'POST',
    timeout: timeout,  // 设置请求超时，单位是毫秒
    json: isJson,
    encoding: null,
    headers: Object.assign({}, baseHeader, {
      'content-type': isJson ? 'application/json' : 'application/x-www-form-urlencoded',
    }, header),
    form: params    // 进行GET请求时，此处的参数一定是qs,请注意，如果是POST请求，参数是form
  }
  return baseRequest(option, isGb2312)
}



// 封装通用 Promise 请求方法
function baseRequest(req_opts, isGb2312 = false) {
  return new Promise((resolve, reject) => {
    request(req_opts, (err, res, data) => {
      if (!err) {
        // 正常返回
        resolve(Object.assign(res, { data: req_opts.json ? data : iconv.decode(data, isGb2312 ? 'gb2312' : 'utf-8').toString() }))
        // resolve({
        //   response: res,
        //   data: data
        // })
      } else {
        // 发生错误
        reject(err)
      }
    });

  })
}