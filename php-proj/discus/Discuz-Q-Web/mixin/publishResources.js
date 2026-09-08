const dayjs = require('dayjs');

module.exports = {
  methods: {
    createAttachmentsDatas(resource) {
      return resource.map(item => {
        const obj = {};
        obj.id = item.id;
        obj.type = 'attachments';
        return obj;
      });
    },
    publishPostResources(sun, post) {
      const imageData = this.createAttachmentsData(post.imageList);
      const attachedData = this.createAttachmentsData(post.attachedList);
      console.log(imageData, attachedData, 1);
      if (imageData.length > 0 || attachedData.length > 0) {
        sun.attachments = [];
        imageData.forEach(item => {
          sun.attachments.push({ id: Number(item.id), type: item.type });
        });
        attachedData.forEach(data => {
          sun.attachments.push({ id: Number(data.id), type: data.type });
        });
      }
      return sun;
    },
    publishpostParamsNum(sun, post) {
      const imageData = this.createAttachmentsData(post.imageList);
      const attachedData = this.createAttachmentsData(post.attachedList);
      if (imageData.length > 0 || attachedData.length > 0) {
        imageData.forEach(item => {
          sun.data.relationships.attachments.data.push({ id: Number(item.id), type: item.type });
        });
        attachedData.forEach(data => {
          sun.data.relationships.attachments.data.push({ id: Number(data.id), type: data.type });
        });
      }
      return sun;
    },
    publishAnswerResources(sun, post) { // 后端在提交回答附件的结构 和 平常的不同
      console.log(post, 2);
      const imageData = this.createAttachmentsData(post.imageList);
      const attachedData = this.createAttachmentsData(post.attachedList);
      if (imageData.length > 0 || attachedData.length > 0) {
        sun.attachments = [];
        imageData.forEach(item => {
          sun.attachments.push({ id: Number(item.id), type: item.type });
        });
        attachedData.forEach(data => {
          sun.attachments.push({ id: Number(data.id), type: data.type });
        });
      }
      return sun;
    },
    publishThreadResources(sun, thread) {
      console.log(thread, 3);
      if (thread.videoList.length > 0) {
        sun.fileId = this.post.videoList[0].id;
        sun.fileName = this.post.videoList[0].name;
      }
      return sun;
    },
    publishPayments(sun, payment) {
      // free 免费， paid 全部付费，attachmentPaid 文章免费，附件付费
      console.log(payment, 4);
      sun.price = 0;
      sun.freeWords = 0;
      sun.attachmentPrice = 0;
      if (payment.paidType === 'attachmentPaid') {
        sun.attachmentPrice = payment.attachmentPrice;
      } else if (payment.paidType === 'paid') {
        sun.price = payment.price;
        sun.freeWords = payment.freeWords;
      }
      return sun;
    },
    publishLocations(sun, location) {
      console.log(location, 5);
      sun.location = location.location ? Number(location.location) : '';
      sun.latitude = location.latitude ? Number(location.latitude) : '';
      sun.longitude = location.longitude ? Number(location.longitude) : '';
      return sun;
    },
    publishQuestions(sun, question) {
      console.log(question, 6);
      sun.question = {};
      const data = sun.question;
      data.type = question.questionType === 'assigner' ? 1 : 0;
      data.price = question.price || 0;
      data.orderId = question.orderId || '';
      if (data.type === 1) {
        data.isOnlooker = question.isOnlooker;
        data.beUserId = this.question.beUser ? Number(this.question.beUser._jv.id) : 0;
      } else if (question.expired_at !== '') {
        data.expiredAt = dayjs(question.expired_at).format('YYYY-MM-DD HH:mm:ss');
      }
      return sun;
    },
    publishProducts(sun, product) {
      console.log(product, 7);
      sun.postGoodsId = Number(product && product._jv && product._jv.id) || '';
      return sun;
    },
    publishProductpostParamsNum(sun, product) {
      console.log(product, 7);
      sun.postGoodsId = Number(product && product._jv && product._jv.id) || '';
      return sun;
    },
    publishRedPackets(sun, redpacket) {
      console.log(redpacket, 8);
      sun.isRedPacket = redpacket.is_red_packet;
      if (redpacket.is_red_packet === 0) {
        return sun;
      }
      // 红包基础信息
      const red = {};
      red.rule = redpacket.rule;
      red.money = redpacket.rule === 0
        ? Number((redpacket.money * redpacket.number).toFixed(2))
        : Number(redpacket.money);
      red.number = redpacket.number;
      red.condition = redpacket.condition;
      red.likenum = redpacket.likenum;
      sun.redPacket = red;
      console.log(Number((redpacket.money * redpacket.number).toFixed(2)), Number(redpacket.money), '价格');
      // 订单信息
      if (redpacket.isPaid) {
        sun.redPacket.orderId = redpacket.orderId;
        sun.redPacket.price = redpacket.money;
      }
      return sun;
    },
    deleteAttachmentsAfterEdits(oldData, newData) {
      console.log(newData, 9);
      const afterEditImageIds = newData.map(item => item.id);
      const beforeEditImageIds = oldData.map(item => item._jv.id);
      const deleteImageIds = beforeEditImageIds.filter(item => afterEditImageIds.indexOf(item) < 0);
      deleteImageIds.forEach(id => {
        this.$store.dispatch('jv/delete', [`/attachments/${id}`, {}]).catch(() => '');
      });
    }
  }
};
