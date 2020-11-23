const req = require('./req')
const utils = require('./utils');
const resModal = require('../config/resModal')
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
      code: resModal.CODE.JWXT_INACCESSIBLE,
      msg: resModal.TEXT.JWXT_INACCESSIBLE
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
        code: resModal.CODE.NO_AUTH,
        msg: regErrMsg.exec(loginRes.data)[1].trim() || '登录教务系统错误'
      };
    default:      //  意料意外的返回状态码
      return {
        ret: false,
        code: resModal.CODE.JWXT_INACCESSIBLE,
        msg: resModal.TEXT.JWXT_INACCESSIBLE
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
      code: resModal.CODE.JWXT_INACCESSIBLE,
      msg: resModal.TEXT.JWXT_INACCESSIBLE
    };
  }
  if (utils.isSessionExpired(myInfo)) {
    return {
      ret: false,
      code: resModal.CODE.COOKIE_EXPIRED,
      msg: resModal.TEXT.COOKIE_EXPIRED
    };
  }
  const regDiv = /<div id="Top1_divLoginName" class="Nsb_top_menu_nc" style="color: #000000;">([^<]*?)<\/div\>/gi;
  const nameAndNum = regDiv.exec(myInfo.data)[1].trim().split('(');
  return {
    ret: true,
    data: {
      name: nameAndNum[0],
      number: nameAndNum[1].substr(0, nameAndNum[1].length - 1),
      isStudent: nameAndNum[1].length > 10
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
    courseRes = await req.post(url, datas, customHeader, false);
  } catch (e) {
    return {
      ret: false,
      code: resModal.CODE.JWXT_INACCESSIBLE,
      msg: resModal.TEXT.JWXT_INACCESSIBLE
    };
  }
  if (utils.isSessionExpired(courseRes)) {
    return {
      ret: false,
      code: resModal.CODE.COOKIE_EXPIRED,
      msg: resModal.TEXT.COOKIE_EXPIRED
    };
  }
  if (!courseRes.data) {
    return {
      ret: false,
      code: resModal.CODE.NOT_FOUND,
      msg: resModal.TEXT.NOT_FOUND
    };
  }

  // 将文本的数据解析为表格数组
  const kbReg = /<table id="kbtable"[\w\W]*?>([\w\W]*?)<\/table>/;
  const coursesContent = utils.tdToArray(kbReg.exec(courseRes.data)[1], true);
  // 去除表头
  coursesContent.shift();
  // 获取备注
  const remarkArr = coursesContent.pop();
  // 采用Set进行课程的存储
  const coursesSet = new Set();

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
          coursesSet.add(`${perCourse}星期{|}${day}`);
        })
      }
    });
  });

  const coursesOutArr = [];
  // 定义课程Object的key
  const keyName = {
    '标题': 'name',
    '老师': 'teacher',
    '周次(节次)': 'week_text',
    '教室': 'classroom',
    '节次': 'session_text',
    '星期': 'day'
  };
  coursesSet.forEach(course => {
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
    let courseWeekText = courseOutObj.week_text;
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
        if (weekDescMode === 0 || (weekDescMode === 1 && week % 2 === 1) || weekDescMode === 2 && week % 2 === 0) {
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

    coursesOutArr.push(courseOutObj);
  })

  return {
    ret: true,
    courses: coursesOutArr,
    remark: remarkArr[1].trim() || null   // 为空则传null
  };
}

// 获取成绩
exports.getGrade = async (cookies, term) => {
  const url = `kscj/cjcx_list`;
  const datas = { kksj: term };
  const customHeader = { 'cookie': cookies };

  let gradeRes;
  try {
    gradeRes = await req.post(url, datas, customHeader, false);
  } catch (e) {
    return {
      ret: false,
      code: resModal.CODE.JWXT_INACCESSIBLE,
      msg: resModal.TEXT.JWXT_INACCESSIBLE
    };
  }
  if (utils.isSessionExpired(gradeRes)) {
    return {
      ret: false,
      code: resModal.CODE.COOKIE_EXPIRED,
      msg: resModal.TEXT.COOKIE_EXPIRED
    };
  }
  if (!gradeRes.data) {
    return {
      ret: false,
      code: resModal.CODE.NOT_FOUND,
      msg: resModal.TEXT.NOT_FOUND
    };
  }
  // 将文本的数据解析为表格数组
  const gradeReg = /<table id="dataList"[\w\W]*?>([\w\W]*?)<\/table>/;
  const gradesContent = utils.tdToArray(gradeReg.exec(gradeRes.data)[1], false, true);

  // 定义表头字段
  const keyName = {
    '序号': 'no',
    '开课学期': 'term',
    '课程编号': 'number',
    '课程名称': 'name',
    '总评成绩': 'grade',
    '学分': 'credit',
    '总学时': 'class_hour',
    '绩点': 'gpa',
    '考核方式': 'exam_type',
    '课程属性': 'properties',
    '课程性质': 'class_type'
  }
  // 最终输出数组
  const gradeOutArr = []
  // 取出实际表头
  const titleName = gradesContent.shift()
  // 取出统计信息
  const countText = gradesContent.pop()[0].trim() || ''
  // 进行单个课程的数据循环
  gradesContent.forEach(gradeArr => {
    const perGradeObj = {}
    gradeArr.forEach((gradeInfo, index) => {
      perGradeObj[keyName[titleName[index]]] = gradeInfo.trim()
    })
    gradeOutArr.push(perGradeObj)
  })

  // 信息统计匹配
  const countReg = /^本学期选课学分：[\s]*([\d.]+)[\s]*获得学分：[\s]*([\d.]+)[\s]*本学期平均学分绩点：[\s]*([\d.]+)[\s]*$/
  const countRegRes = countReg.exec(countText) || {}
  const countObj = {
    credit_expected: countRegRes[1] || null,
    credit_gained: countRegRes[2] || null,
    gpa: countRegRes[3] || null
  }
  return {
    ret: true,
    grade: gradeOutArr,
    count: countObj
  };
}

// 查询空教室
exports.getEmptyRoom = async (cookies, term, buildid, week, day, session) => {
  const url = `kbxx/jsjy_query2`;
  const datas = {
    typewhere: 'jszq',
    xnxqh: term,
    xqid: 'Vn',
    jxlbh: buildid,
    zc: week,
    zc2: week,
    xq: day,
    xq2: day,
    jc1: session,
    jc2: session
  };
  const customHeader = { 'cookie': cookies };

  let roomRes;
  try {
    roomRes = await req.post(url, datas, customHeader, false);
  } catch (e) {
    return {
      ret: false,
      code: resModal.CODE.JWXT_INACCESSIBLE,
      msg: resModal.TEXT.JWXT_INACCESSIBLE
    };
  }
  if (utils.isSessionExpired(roomRes)) {
    return {
      ret: false,
      code: resModal.CODE.COOKIE_EXPIRED,
      msg: resModal.TEXT.COOKIE_EXPIRED
    };
  }
  if (!roomRes.data) {
    return {
      ret: false,
      code: resModal.CODE.NOT_FOUND,
      msg: resModal.TEXT.NOT_FOUND
    };
  }
  // 将文本的数据解析为表格数组
  const roomReg = /<table id="dataList"[\w\W]*?>([\w\W]*?)<\/table>/;
  const roomsContent = utils.tdToArray(roomReg.exec(roomRes.data)[1], false, true);
  // 定义教室状态模板
  const roomStatus = {
    'Ｌ': '临调',
    'Ｇ': '调课',
    '': '空闲',
    'Κ': '考试',
    'Ｘ': '锁定',
    'Ｊ': '借用',
    '◆': '上课'
  }
  // 最终输出数组
  const roomsOutArr = []
  // 删除最前面无用的星期行数
  roomsContent.shift();
  // 删除最后面无用的空行
  roomsContent.pop();
  // 取出表头
  const sessionTitle = roomsContent.shift();
  // 去除第一个空白单元格内容
  sessionTitle.shift()
  // 循环每个课室并解析数据
  roomsContent.forEach(room => {
    const roomObj = {
      title: null,
      capacities: 0,
      status: []
    };
    const roomName = room.shift();
    const titleReg = /([\w\W]*?)\(([\d]*)\/([\d]*)\)/;
    const titleRegRes = titleReg.exec(roomName);
    roomObj.title = titleRegRes[1];
    roomObj.capacities = parseInt(titleRegRes[2]);
    room.forEach(roomCell => {
      roomObj.status.push(roomStatus[roomCell])
    })
    roomsOutArr.push(roomObj);
  })
  return {
    ret: true,
    sessionTitle: sessionTitle,
    roomInfo: roomsOutArr
  };
}