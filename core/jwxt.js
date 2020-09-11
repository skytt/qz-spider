const req = require('./req')
const utils = require('./utils');
require('tls').DEFAULT_MIN_VERSION = 'TLSv1';   // 兼容教务系统TLS1.1

// 进行登录并在返回值携带cookies
exports.doLogin = async (username, password) => {
  const url = `xk/LoginToXk`;
  const datas = {
    encoded: utils.encodeInp(username) + '%%%' + utils.encodeInp(password)
  };

  let loginRes;
  try {
    loginRes = await req.post(url, datas, null, true);
  } catch (e) {
    return {
      ret: false,
      code: 406,
      msg: '访问教务系统时发生未知错误'
    };
  }
  const cookies = loginRes.headers['set-cookie'][0];
  switch (loginRes.statusCode) {
    case 302:      // 登录成功，302是因为需要跳转到主界面，此时cookies有效
      return {
        ret: true,
        data: cookies
      };
    case 200:       // 跳转回登录页，证明出现了登录错误，捕获错误类型
      const regErrMsg = /<font style="display: inline;white-space:nowrap;" color="red">([^<]*?)<\/font\>/gi;
      return {
        ret: false,
        code: 401,
        msg: regErrMsg.exec(loginRes.data)[1].trim() || '未能成功登录教务系统'
      };
    default:      //  意料意外的返回状态码
      return {
        ret: false,
        code: 406,
        msg: `访问教务系统时发生未知错误`
      };
  }
}

// 获取个人资料
exports.getMyInfo = async (cookies) => {
  const url = `framework/xsMain.jsp`;
  const customHeader = { 'cookie': cookies };

  let myInfo;
  try {
    myInfo = await req.get(url, null, customHeader);
  } catch (e) {
    return {
      ret: false,
      code: 406,
      msg: '访问教务系统时发生未知错误'
    };
  }
  if (utils.isSessionExpired(myInfo)) {
    return {
      ret: false,
      code: 407,
      msg: 'Token已失效'
    };
  }
  const regDiv = /<div id="Top1_divLoginName" class="Nsb_top_menu_nc" style="color: #000000;">([^<]*?)<\/div\>/gi;
  const nameAndNum = regDiv.exec(myInfo.data)[1].trim().split('(');
  return {
    ret: true,
    data: {
      name: nameAndNum[0],
      number: nameAndNum[1].substr(0, nameAndNum[1].length - 1)
    }
  };
}

// 获取课程
exports.getCourses = async (cookies, term, zc = null) => {
  const url = `xskb/xskb_list.do`;
  const datas = { xnxq01id: term };
  if (zc) { datas[zc] = zc };
  const customHeader = { 'cookie': cookies };

  let courseRes;
  try {
    courseRes = await req.post(url, datas, customHeader, !true);
  } catch (e) {
    return {
      ret: false,
      code: 406,
      msg: '访问教务系统时发生未知错误'
    };
  }
  if (utils.isSessionExpired(courseRes)) {
    return {
      ret: false,
      code: 407,
      msg: 'Token已失效'
    };
  }
  if (!courseRes.data) {
    return {
      ret: false,
      code: 404,
      msg: '没有找到对应的课表'
    };
  }

  // 将文本的数据解析为表格数组
  const coursesContent = utils.tdToArray(utils.reg_table.exec(courseRes.data)[1], true);
  // 去除表头
  coursesContent.shift();
  // 获取备注
  const remarkArr = coursesContent.pop();
  // 采用Set进行课程的存储
  const courseSet = new Set();

  // 进行表格数组处理
  coursesContent.map(row => {
    // 去除开头的 “第*节” 标识
    row.shift();
    // 单节课循环
    row.forEach((course, day) => {
      // 删首尾空
      const courseCellText = course.trim();
      if (courseCellText) {
        // 课程内容不为空，拆分多节课程后（如有），计入Set中
        const courseCellArr = courseCellText.split('{@@}');
        courseCellArr.forEach(perCourse => {
          courseSet.add(`${perCourse}星期{|}${day}`);
        })
      }
    });
  });

  const courseOutArr = [];
  // 定义课程Object的key
  const keyName = {
    '标题': 'name',
    '老师': 'teacher',
    '周次(节次)': 'week_text',
    '教室': 'classroom',
    '节次': 'session_text',
    '星期': 'day'
  };
  courseSet.forEach(course => {
    const courseOutObj = {};

    // 解析课程文本的基本数据
    const courseInfoArr = course.split('{||}');
    // 去除重复的课程标题
    courseInfoArr.shift();
    // 循环每项信息进行 key-value 的解析
    courseInfoArr.forEach(infoText => {
      const infoArr = infoText.split('{|}');
      if (infoArr.length === 1) {
        // 没有key，对应为课程标题
        courseOutObj[keyName['标题']] = infoArr[0].trim();
      } else {
        // 有key，对应课程信息
        courseOutObj[keyName[infoArr[0].trim()]] = infoArr[1].trim();
      }
    })

    // 解析显示的周次，生成对应数组
    let courseWeekText = courseOutObj.weektext;
    if (courseWeekText) {
      const weekArr = [];    //输出周次数组
      let weekDescMode;   // 模式说明：0-全周，1单周，2-双周
      if (courseWeekText.endsWith('单周')) {
        weekDescMode = 1;
      } else if (courseWeekText.endsWith('双周')) {
        weekDescMode = 2;
      } else {
        weekDescMode = 0;
      }
      // 判断传入的周次是否符合单双周模式的描述（不能直接依靠开始结束循环，会有例如「1-18单周」这种东西的描述，很坑）
      const isWeekLeagl = week => {
        if (weekDescMode === 0 || (weekDescMode === 1 && week % 2 === 1) || weekDescMode === 3 && week % 2 === 0) {
          return true;
        }
        return false;
      }
      // 进行周次循环，将上课的周次推入week数组
      courseWeekText = courseWeekText.replace(/[\u4e00-\u9fa5]/g, '');
      courseWeekTextArr = courseWeekText.split(',');
      courseWeekTextArr.forEach(weekRangeText => {
        if (weekRangeText.indexOf('-') === -1) {
          // 剩下单周，如13，判断是否可以直接推入结果数组
          if (isWeekLeagl(weekRangeText)) {
            weekArr.push(parseInt(weekRangeText));
          }
        } else {
          // 是周次时间段，如2-6
          weekRangeTextArr = weekRangeText.split('-');
          for (let i = parseInt(weekRangeTextArr[0]); i <= parseInt(weekRangeTextArr[1]); i++) {
            if (isWeekLeagl(i)) {
              weekArr.push(i);
            }
          }
        }
      })
      courseOutObj.week = weekArr;
    }

    // 解析上课节次
    const courseSessionText = courseOutObj.session_text;
    if (courseSessionText) {
      if (courseSessionText.indexOf('-') === -1) {
        // 单节课程
        courseOutObj.session_start = parseInt(courseSessionText);
        courseOutObj.session_end = parseInt(courseSessionText);
      } else {
        // 范围课程
        courseSessionTextArr = courseSessionText.split('-');
        courseOutObj.session_start = parseInt(courseSessionTextArr[0]);
        courseOutObj.session_end = parseInt(courseSessionTextArr[courseSessionTextArr.length - 1]);
      }
    }

    // 星期day字段整型化
    courseOutObj.day = parseInt(courseOutObj.day);

    courseOutArr.push(courseOutObj);
  })

  return {
    ret: true,
    courses: courseOutArr,
    remark: remarkArr[1].trim() || null   // 为空则传null
  };
}