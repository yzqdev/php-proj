/**
 * 英文 - 只是列举出来了部分时间和提示相关的文案，这个可以根据具体需求进行更改和添加
 * 注意和中文或者其它语言对齐
 */
export default {
  discuzq: {
    // 测试，不用的时候可以删掉
    hello: 'Hello',
    // 常用
    ok: 'Ok',
    close: 'Close',
    // 日期相关
    date: {
      now: 'Now',
      today: 'Today',
      year: '',
      month1: 'January',
      month2: 'February',
      month3: 'March',
      month4: 'April',
      month5: 'May',
      month6: 'June',
      month7: 'July',
      month8: 'August',
      month9: 'September',
      month10: 'October',
      month11: 'November',
      month12: 'December',
      week: 'week',
      weeks: {
        sun: 'Sun',
        mon: 'Mon',
        tue: 'Tue',
        wed: 'Wed',
        thu: 'Thu',
        fri: 'Fri',
        sat: 'Sat'
      },
      months: {
        jan: 'Jan',
        feb: 'Feb',
        mar: 'Mar',
        apr: 'Apr',
        may: 'May',
        jun: 'Jun',
        jul: 'Jul',
        aug: 'Aug',
        sep: 'Sep',
        oct: 'Oct',
        nov: 'Nov',
        dec: 'Dec'
      },
      morning: 'morning',
      afternoon: 'afternoon'
    },
    // 列表加载提示相关
    list: {
      loading: 'Loading',
      noMatch: 'No matching data',
      noData: 'No data',
      noMoreData: 'No more data！'
    },
    // 提示框
    msgbox: {
      title: 'Message',
      confirm: 'OK',
      cancel: 'Cancel'
    },
    // 上传
    upload: {
      delete: 'Delete',
      preview: 'Preview',
      continue: 'Continue'
    },
    // 图片
    image: {
      error: 'FAILED',
      imageUploading: 'Image uploading...'
    },
    // 导航
    pageHeader: {
      title: 'Back'
    },
    // @人员页面
    atMember: {
      atTitle: 'Select @ member',
      selectedMember: 'Selected member',
      notSelected: 'Not selected',
      selected: '@ Selected'
    },
    role: {
      noRole: 'No role'
    },
    // 发布页
    post: {
      note: 'You can also enter {num} words',
      placeholder: 'What do you want to say ...',
      post: 'Post',
      paymentAmount: 'Payment amount',
      freeWordCount: 'Free word count',
      selectToViewFreeWordCount: 'Select to view free word count',
      chooseCategory: 'Choose category',
      selectToViewPaymentAmount: 'Select to view payment amount',
      cancel: 'Cancel',
      enterToViewPaymentAmount: 'Enter to view payment amount',
      enterTheWordCount: 'Enter the word count',
      free: 'Free',
      yuan: 'yuan',
      customize: 'Customize',
      theContentCanNotBeBlank: 'the content can not be blank',
      imageCannotBeEmpty: 'Image cannot be empty',
      videoCannotBeEmpty: 'Video cannot be empty',
      pleaseWaitForTheVideoUploadToComplete:
        'Please wait for the video upload to complete',
      failedToObtainSignature: 'Failed to obtain signature',
      fromWeChatApplet: 'From WeChat applet'
    },
    // 站点管理页面
    manage: {
      payJoin: 'Pay Join',
      payInfoTitle: 'You need to pay for visit',
      inviteInfoTitle: 'You are invite to visit',
      inviteMembers: 'Invite members',
      siteMembers: 'site of members',
      siteManagement: 'master of management',
      manageMembers: 'Manage members',
      searchMembers: 'Search members',
      nouse: 'nouse',
      used: 'used',
      invalid: 'invalid',
      overdue: 'overdue',
      setInvalid: 'Set invalid',
      generateInvitationUrl: 'Generate invitation url',
      notSelected: 'notSelected',
      selected: 'selected',
      total: 'there are {total} records',
      member: 'member',
      contents: 'contents',
      share: 'share',
      circleinfo: 'circleinfo',
      siteintroduction: 'siteintroduction',
      creationtime: 'creationtime',
      circlemode: 'circlemode',
      paymentmode: 'paymentmode',
      publicmode: 'publicmode',
      circlemaster: 'circlemaster',
      myRole: 'myRole',
      joinedTime: 'joinedTime',
      periodvalidity: 'periodvalidity',
      myauthority: 'myauthority',
      setting: 'setting',
      noContent: 'No Content',
      contentMaxLength: 'No more than 450 words'
    },
    // 消息页面
    notice: {
      notice: 'notice',
      relate: '@ Me',
      relatedMe: ' @ me',
      reply: 'Reply Me',
      repliedMe: ' replied me',
      like: 'Like Me',
      likedMe: ' Liked me',
      reward: 'Reward Me',
      rewardedMe: ' Rewarded me',
      payedMe: ' Payed me',
      system: 'System notices',
      admin: 'Admin',
      delete: 'Delete',
      send: 'Send',
      emptycontent: 'the content can not be empty',
      approved: 'Your withdrawal has been approved, please note that check.',
      unapproved:
        'Your withdrawal review has been rejected, the reason for rejection: the review did not pass.'
    }
  },
  // 信息修改页
  modify: {
    user: '用户',
    nametitle: '修改用户名',
    mobiletitle: '修改手机号',
    newPwdTitle: '设置新密码',
    setphontitle: '设置手机号',
    paypwdtitle: '设置支付密码',
    withdratitle: '提现',
    edipwdtitle: '修改密码',
    signaturetitle: '签名',
    realnametitle: '实名认证',
    findpawdtitle: '找回密码',
    authontitle: '验证身份',
    numbermodifitions: '用户名只可修改一次',
    submit: '提交',
    emptyname: '名字不能为空',
    modifysucc: '名字修改成功',
    phonbound: '已绑定手机',
    placeEnterCode: '请输入短信验证码',
    placeEnterRegisteredPhone: '请输入您注册用的手机号码',
    editphonecode: 'please input your phone code',
    valifailed: '验证失败',
    lateron: '稍后重试',
    sendVerifyCode: '获取验证码',
    phonnumberempty: '未绑定手机号',
    retransmission: '秒后重发',
    validionerro: '验证码错误，您还可以重发',
    frequency: '次',
    newphonnumber: '输入新手机号码',
    phontitle: '手机号修改成功',
    nextsetp: '下一步',
    setpasswordtip: 'for you safety use 6 to set password',
    editpasswordtip: 'for you safety input your orign password',
    enterpaymentpas: '请输入支付密码',
    enterpaymentagin: '请再次输入支付密码',
    modification: '已有支付密码',
    reenter: '两次输入的密码不同，请重新输入',
    paymentsucceed: '支付密码设置成功',
    payee: '收款人',
    withdrawable: '可提现余额',
    withdrawalamount: '提现金额',
    actualamout: '实际提现金额',
    enteramount: '请输入提现金额',
    phonnumber: '手机号',
    servicechaege: '手续费：',
    percentage: '元 (',
    percentagcon: '%)',
    withdrawal: '提现提交成功',
    enterold: '请输入旧密码',
    enterNew: '请输入新密码',
    enterNewRepeat: '请重复输入新密码',
    oldpassword: '旧密码不能为空',
    newpassword: '新密码不能为空',
    confrimpasword: '确认密码不能为空',
    titlepassword: '密码修改成功',
    forgetoldpassword: '忘记旧密码?',
    masstext: '两次输入的密码不一致，请重新输入',
    realname: '请输入真实姓名',
    enteridnumber: '请输入您的身份证号码',
    idcardisempty: '身份证号码不能为空',
    nameauthensucc: '实名认证成功',
    mysignture: '我的签名',
    canalsoinput: '还能输入',
    wordnumber: '个字',
    signturecontent: '请输入签名内容',
    modificationsucc: '签名设置成功',
    passwordsetsucc: '密码设置成功',
    placeenternewpass: '请输新密码',
    authentication: '请输入支付密码，以验证身份',
    authensucceeded: '身份验证成功',
    authenfailed: '身份验证失败',
    passwordinputerro: '密码输入错误',
    forgetmanypassword: '忘记密码？',
    nohasphon: '请先绑定手机号',
    idtitl: '非法身份证号（长度、校验位等不正确）',
    nametitl2: '非法姓名（长度、格式等不正确）',
    verifyoldphon: '验证旧手机',
    greaterthan: '提现金额不能大于可提现余额',
    NoteOpen: '短信服务未开启',
    logoinpaswd: '已有登录密码',
    forgetPassword: '忘记密码 ',
    retrievePassword: ' 找回密码',
    retrievePasswordByPhone: '通过手机找回密码',
    resetPassword: '请重新设置您的登录密码',
    resetPasswordSuccess: '设置新密码成功',
    resetPasswordSuccessRepeat: '重新设置新密码成功',
    resetPasswordLoginTip:
      '请重新返回登录页面，并使用新设密码进行登录操作请点击下方登录。'
  },
  // 我的和个人主页
  profile: {
    uploadFile: 'Upload file',
    filesizecannotexceed: 'File size cannot exceed',
    pleaseselect: 'Please select',
    fileformat: 'File format',
    downloadSuccess: 'Download success',
    downloadError: 'download error',
    thetemporarypathis: 'The temporary path is',
    attachment: 'Attachment',
    post: 'Post',
    notice: 'Notice',
    mine: 'Mine',
    backhomePage: 'Backhome page',
    myprofile: 'My profile',
    mywallet: 'My wallet',
    myfavorite: 'My favorite',
    circleinfo: 'Circle information',
    search: 'Search',
    privateMessage: 'privateMessage',
    circlemanagement: 'Circle management',
    topic: 'Topic',
    following: 'Following',
    followed: 'Followed',
    mutualfollow: 'Mutual follow',
    followers: 'Followers',
    likes: 'Likes',
    username: 'Username',
    avatar: 'Avatar',
    mobile: 'Mobile',
    bindingmobile: 'Binding mobile',
    password: 'Password',
    modify: 'Modify',
    wechat: 'Wechat',
    certification: 'Certification',
    tocertification: 'To certification',
    signature: 'Signature',
    availableamount: 'Available amount',
    freezeamount: 'Freeze amount',
    withdrawalslist: 'Withdrawals list',
    walletlist: 'Walletlist',
    orderlist: 'Orderlist',
    walletpassword: 'Wallet password',
    setpassword: 'Set password',
    editpaypassword: 'Edit pay password',
    orignpassword: 'Input orign password',
    setpaypassword: 'Set pay password',
    total: 'Total',
    records: 'Records',
    amountinvolved: 'Amount involved',
    collection: 'Collection',
    item: 'Item',
    status: 'Status',
    time: 'Time',
    paid: 'Paid',
    tobepaid: 'To be paid',
    all: 'All',
    type: 'Type',
    register: 'Register',
    reward: 'Reward',
    paytheme: 'Pay theme',
    paygroup: 'Pay group',
    withdrawalfreeze: 'Withdrawal freeze',
    withdrawalsuccess: 'Withdrawal success',
    withdrawalunfreeze: 'Withdrawal unfreeze',
    registeredincome: 'Registered income',
    rewardincome: 'Reward income',
    laborincome: 'Labor income',
    laborexpenditure: 'Labor expenditure',
    tobereviewed: 'To be reviewed',
    approved: 'Approved',
    auditfailed: 'Audit failed',
    paymenttobemade: 'Payment to bemade',
    paymentsucceed: 'Payment succeed',
    paymentfailed: 'Payment failed',
    freezingreason: 'Freezing reason',
    theuserwasdeleted: 'The user was deleted',
    thethemewasdeleted: 'The theme was deleted',
    givearewardforyourtheme: 'Give a reward for your theme',
    givearewardforthetheme: 'Give a reward for thetheme',
    paidtoseeyourtheme: 'Paid to see your theme',
    paidtoview: 'Paid to view',
    orderexpired: 'Order expired',
    payfail: 'Pay fail',
    cancelorder: 'Cancel order',
    permanent: 'Permanent',
    personalhomepage: 'Personal homepage',
    successfullyuploadedtheavatar: 'Successfully uploaded the Avatar',
    validationerror: 'Validation error',
    uploadtimenotup: 'Upload time not up'
  },
  site: {
    circleintroduction: 'Circle introduction',
    creationtime: 'Creation time',
    periodvalidity: 'Period of validity',
    day: 'Day',
    circlemaster: 'Circle master',
    paynow: 'Pay now',
    justonelaststepjoinnow: 'Just one last step ,join now',
    circlemode: 'Circle mode',
    paymentmode: 'Payment mode',
    publicmode: 'Public mode',
    validfromaccession: 'Valid from accession',
    myauthority: 'My authority',
    inviteyouas: 'Invite you as',
    join: 'Join',
    accepttheinvitationandbecome: 'Accept the invitation and become',
    continueResgister: 'Continue resgister'
  },
  // 搜索
  search: {
    filter: 'Filter',
    search: 'Search',
    searchusers: 'Search users',
    searchthemes: 'Search themes',
    searchkeywords: 'Search keywords',
    cancel: 'Cancel',
    users: 'Users',
    searchmoreusers: 'Search more users',
    norelatedusersfound: 'No related users found',
    themes: 'Themes',
    searchmorethemes: 'Search more themes',
    norelatedthemesfound: 'No related themes found'
  },
  // 登录注册
  user: {
    status: 'remember my status',
    phonelogin: 'PhoneLogin',
    userlogin: 'UserLogin',
    quicklogin: 'QuickLogin',
    login: 'Login',
    register: 'Register',
    registerReason: 'Register reason',
    submit: 'Submit',
    username: 'Please input username',
    password: 'Please input password',
    reason: 'Please input reason',
    phoneNumber: 'Please input phoneNumber',
    pwd: 'Please input password',
    verification: 'verificationCode',
    verificationCode: 'Please input verificationCode',
    exist: 'An existing account? Login',
    noexist: 'There is no account? Register',
    forgetPassword: 'forget password?',
    phoneNumberLogin: 'Phone number login',
    verificationCodeLogin: 'Verification code login',
    passwordLogin: 'Password to login',
    sendVerificationCode: 'Send verification code',
    registerBindId: 'Register and bind WeChat ID',
    registerBind: 'Register and bind WeChat',
    loginBindId: 'Login and bind WeChat ID',
    loginBind: 'Login and bind WeChat'
  },
  core: {
    // 主要用于游客没有相关权限的提示 http://bug.eims.com.cn/bug-view-40.html
    back_home: 'back to home',
    back_login: 'back to login',
    permission_denied2: 'The user group you belong to has no permission to access the current page',
    permission_denied3: 'No permission to view',
    permission_denied4: 'You do not have permission to view the content of this page'
  }
};