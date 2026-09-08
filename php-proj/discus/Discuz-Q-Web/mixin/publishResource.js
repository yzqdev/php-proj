const dayjs = require('dayjs');

module.exports = {
  methods: {
    createAttachmentsData(resource) {
      return resource.map(item => {
        const obj = {};
        obj.id = item.id;
        obj.type = 'attachments';
        return obj;
      });
    },
    publishPostResource(params, post) {
      const imageData = this.createAttachmentsData(post.imageList);
      const attachedData = this.createAttachmentsData(post.attachedList);
      if (imageData.length > 0 || attachedData.length > 0) {
        params._jv.relationships.attachments = {};
        params._jv.relationships.attachments.data = [];
        params._jv.relationships.attachments.data.push(...imageData);
        params._jv.relationships.attachments.data.push(...attachedData);
      }
      return params;
    },
    publishAnswerResource(params, post) { // 后端在提交回答附件的结构 和 平常的不同
      const imageData = this.createAttachmentsData(post.imageList);
      const attachedData = this.createAttachmentsData(post.attachedList);
      if (imageData.length > 0 || attachedData.length > 0) {
        params.relationships.attachments = {};
        params.relationships.attachments.data = [];
        params.relationships.attachments.data.push(...imageData);
        params.relationships.attachments.data.push(...attachedData);
      }
      return params;
    },
    publishThreadResource(params, thread) {
      if (thread.videoList.length > 0) {
        params.file_id = this.post.videoList[0].id;
        params.file_name = this.post.videoList[0].name;
      }
      return params;
    },
    publishPayment(params, payment) {
      // free 免费， paid 全部付费，attachmentPaid 文章免费，附件付费
      params.price = 0;
      params.free_words = 0;
      params.attachment_price = 0;
      if (payment.paidType === 'attachmentPaid') {
        params.attachment_price = payment.attachmentPrice;
      } else if (payment.paidType === 'paid') {
        params.price = payment.price;
        params.free_words = payment.freeWords;
      }
      return params;
    },
    publishLocation(params, location) {
      params.location = location.location ? location.location : '';
      params.latitude = location.latitude ? location.latitude : '';
      params.longitude = location.longitude ? location.longitude : '';
      return params;
    },
    publishQuestion(params, question) {
      params._jv.relationships.question = {};
      params._jv.relationships.question.data = {};
      const data = params._jv.relationships.question.data;
      data.type = question.questionType === 'assigner' ? 1 : 0;
      data.price = question.price || 0;
      data.order_id = question.orderId || '';
      if (data.type === 1) {
        data.is_onlooker = question.isOnlooker;
        data.be_user_id = this.question.beUser ? this.question.beUser._jv.id : 0;
      } else if (question.expired_at !== '') {
        data.expired_at = dayjs(question.expired_at).format('YYYY-MM-DD HH:mm:ss');
      }
      return params;
    },
    publishProduct(params, product) {
      params.post_goods_id = (product && product._jv && product._jv.id) || '';
      return params;
    },
    publishRedPacket(params, redpacket) {
      params.is_red_packet = redpacket.is_red_packet;
      if (redpacket.is_red_packet === 0) {
        return params;
      }
      // 红包基础信息
      const red = {};
      red.rule = redpacket.rule;
      red.money = redpacket.rule === 0
        ? (redpacket.money * redpacket.number).toFixed(2)
        : redpacket.money;
      red.number = redpacket.number;
      red.condition = redpacket.condition;
      red.likenum = redpacket.likenum;
      params.redPacket = red;
      // 订单信息
      if (redpacket.isPaid) {
        params._jv.relationships.redpacket = {};
        params._jv.relationships.redpacket.data = {};
        const data = params._jv.relationships.redpacket.data;
        data.order_id = redpacket.orderId;
        data.price = redpacket.money;
      }
      return params;
    },
    deleteAttachmentsAfterEdit(oldData, newData) {
      const afterEditImageIds = newData.map(item => item.id);
      const beforeEditImageIds = oldData.map(item => item._jv.id);
      const deleteImageIds = beforeEditImageIds.filter(item => afterEditImageIds.indexOf(item) < 0);
      deleteImageIds.forEach(id => {
        this.$store.dispatch('jv/delete', [`/attachments/${id}`, {}]).catch(() => '');
      });
    }
  }
};
