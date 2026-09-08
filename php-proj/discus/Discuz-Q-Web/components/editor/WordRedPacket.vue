<template>
  <div class="word-pay">
    <div v-if="!isEdit" class="open-box-btn">
      <div class="open-title">发布红包：</div>
      <div class="open-btn" @click="openRedPacket">
        {{ redpacket.is_red_packet === 0 ? '+ 添加红包' : ' 修改红包' }}
      </div>
    </div>
    <!-- 红包描述信息 -->
    <div v-show="redpacket.is_red_packet === 1" class="red-desc">
      <span class="desc-title">发放规则：</span>
      <span class="desc-value">
        {{ redpacket.rule === 0 ? '定额' : '随机' }}
      </span>
      <span class="desc-title">红包总金额：</span>
      <span v-if="redpacket.rule === 0" class="desc-value">
        {{ (redpacket.money*redpacket.number).toFixed(2) }}元
      </span>
      <span v-else class="desc-value">
        {{ redpacket.money }}元
      </span>
      <span class="desc-title">红包个数：</span>
      <span class="desc-value">
        {{ redpacket.number }}个
      </span>
      <span class="desc-title">获得条件：</span>
      <span class="desc-value">
        {{ redpacket.condition === 0 ? `回复领取` : `集赞${ redpacket.likenum }个领取` }}
      </span>
    </div>
    <!-- 红包弹框 -->
    <div v-show="showRedPacketBox && !isEdit" class="redpacket-box">
      <div class="redpacket-content">
        <div class="redpacket-head">
          {{ redpacket.is_red_packet === 0 ? '添加红包' : ' 修改红包' }}
          <svg-icon type="close" @click="closeRedPacket" />
        </div>
        <div class="word-pay-item">
          <div class="word-pay-title">发放模式：</div>
          <el-radio
            v-model="redpacket.rule"
            :label="1"
            @change="val => $emit('changeRedRule', val)"
          >随机</el-radio>
          <el-radio
            v-model="redpacket.rule"
            :label="0"
            @change="val => $emit('changeRedRule', val)"
          >定额</el-radio>
        </div>
        <div class="word-pay-item">
          <div class="word-pay-title">
            {{ redpacket.rule === 1 ? "红包总金额：" : "单个红包金额：" }}
          </div>
          <el-input
            v-model="redpacket.money"
            type="number"
            placeholder="请输入红包金额"
            @change="handleRedMoney"
          />
          <span v-show="redpacket.money === 0" class="tip">
            可输入红包金额范围 0.01 ~ 200 元
          </span>
        </div>
        <div class="word-pay-item">
          <div class="word-pay-title">红包个数：</div>
          <el-input
            v-model="redpacket.number"
            type="number"
            placeholder="请输入红包个数"
            @change="handleRedNumber"
          />
          <span v-show="redpacket.number === 0" class="tip">
            可输入红包个数 1 ~ 100 个
          </span>
        </div>
        <div class="word-pay-item">
          <div class="word-pay-title">获得条件：</div>
          <el-radio
            v-model="redpacket.condition"
            :label="0"
            @change="val => $emit('changeRedCondition', val)"
          >回复帖子</el-radio>
          <el-radio
            v-model="redpacket.condition"
            :label="1"
            @change="val => $emit('changeRedCondition', val)"
          >点赞数</el-radio>
          <el-input
            v-show="redpacket.condition === 1"
            v-model="redpacket.likenum"
            type="number"
            class="input_likenum"
            placeholder="集赞数"
            @change="handleRedlikenum"
          />
          <span v-show="redpacket.condition === 1">个</span>
          <span
            v-show="redpacket.condition === 1 && redpacket.likenum === 0"
            class="tip"
          >
            可输入集赞数 1 ~ 250 个
          </span>
        </div>
        <div class="btn-container">
          <el-button class="btn-confirm" @click="selectRedPacket">确定</el-button>
          <el-button class="btn-cancle" @click="cancelRedPacket">取消</el-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'WordRedPacket',
  props: {
    redpacket: {
      type: Object,
      default: () => {}
    },
    isEdit: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      // is_red_packet: 0, // 文字帖类型 0-普通帖 1-红包贴
      // rule: 1, // 红包贴模式 0-定额 1-随机
      // money: '', // 红包金额
      // number: '', //  红包数量
      // condition: 0, // 红包获得条件 0-回复 1-集赞
      // likenum: 0 // 集赞数
      showRedPacketBox: false
    };
  },
  methods: {
    handleRedMoney(val) {
      if (
        /^(([1-9][0-9]*)|[0])(\.\d{1,2})?$/.test(val)
        && val >= 0.01
        && val <= 200
      ) {
        return this.$emit('changeRedMoney', val);
      }
      this.$emit('changeRedMoney', 0);
    },
    handleRedNumber(val) {
      if (/^([1-9][0-9]*)$/.test(val) && val <= 100) {
        return this.$emit('changeRedNumber', val);
      }
      this.$emit('changeRedNumber', 0);
    },
    handleRedlikenum(val) {
      if (/^([1-9][0-9]*)$/.test(val) && val <= 250) {
        return this.$emit('changeRedlikenum', val);
      }
      this.$emit('changeRedlikenum', 0);
    },
    openRedPacket() {
      this.showRedPacketBox = true;
    },
    closeRedPacket() {
      this.showRedPacketBox = false;
    },
    selectRedPacket() {
      this.$emit('changeRedType', 1);

      if (+this.redpacket.money === 0) {
        this.$message.warning('请确认红包金额，0.01 ~ 200 元！');
        return this.$emit('changeRedType', 0);
      } else if (+this.redpacket.number === 0) {
        this.$message.warning('请确认红包个数，1 ~ 100 个！');
        return this.$emit('changeRedType', 0);
      } else if (
        this.redpacket.condition === 1
        && +this.redpacket.likenum === 0
      ) {
        this.$message.warning('请确认集赞数，1 ~ 250 个！');
        return this.$emit('changeRedType', 0);
      } else if (this.redpacket.rule === 1) {
        const isUnreasonable = this.redpacket.money < this.redpacket.number * 0.01;
        if (isUnreasonable) {
          this.$message.warning('请匹配合理的红包金额与个数！');
          return this.$emit('changeRedType', 0);
        }
      }

      this.showRedPacketBox = false;
    },
    cancelRedPacket() {
      this.$emit('changeRedType', 0);
      this.showRedPacketBox = false;
    }
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/css/variable/color.scss';
@import '@/assets/css/variable/mixin.scss';
@include custom-el-input-style();
/*IFTRUE_default*/
::v-deep .el-input .el-input__inner:focus {
  border-color: #409EFF;
}
.word-pay{
  .open-box-btn{
    display:flex;
    align-items:center;
    height:70px;
    font-weight: bold;
    color: #6d6d6d;
    .open-btn{
      font-weight: normal;
      color: #1878F3;
      cursor: pointer;
    }
  }
  // 描述信息
  .red-desc{
    display: flex;
    align-items: center;
    height: 50px;
    font-size: 16px;
    .desc-title{
      color: #999;
    }
    .desc-value{
      color: #333;
      padding-right: 20px;
      border-right: 1px solid #ddd;
      margin-right: 20px;
      &:last-child{
        border-right: none;
      }
    }
  }
  // 弹框
  .redpacket-box{
    position: fixed;
    left: 0;
    top: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,.5);
    z-index: 99;
    .redpacket-content{
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      left: 0;
      margin: auto;
      width: 620px;
      height: 440px;
      font-size: 16px;
      color: #333;
      border-radius: 10px;
      background: #fff;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      .redpacket-head{
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        height: 50px;
        .svg-icon-close {
          font-size: 14px;
          fill: #6d6d6d;
          cursor: pointer;
        }
      }
      .word-pay-item{
        position: relative;
        display:flex;
        align-items:center;
        padding: 0 20px;
        height:70px;
        font-size: 14px;
        color: #606266;
        .word-pay-title{
          flex:0 0 120px;
          font-size: 16px;
          color: #333;
        }
        .el-input{
          width: 420px;
        }
        .input_likenum{
          width: 90px;
          margin-right: 10px;
        }
        .tip {
          position: absolute;
          bottom: -4px;
          left: 140px;
          font-size: 12px;
          color: red;
        }
      }

      .btn-container{
        display: flex;
        justify-content: flex-end;
        align-items: center;
        height: 50px;
        padding: 0 20px;
        background: #f4f7f7;
        .el-button{
          padding: 10px 20px;
          margin-right: 20px;
          border-radius: 0 !important;
        }
        .btn-confirm{
          color: #fff ;
          background: #1878f3;
          border-color: transparent !important;
        }
        .btn-cancle{
          color: #333 ;
          background: #fff;
          border-color: #dec0e6 !important;
          &:hover{
            border: 1px solid #d4e6fc;
            background: #e5f2ff !important
          }
        }
      }
    }
  }
}
/*FITRUE_default*/
/*IFTRUE_pay*/
@import "@/assets/css/variable/color_pay.scss";
@import '@/assets/css/pay_scss/redpacket.scss';
@include custom-el-radio-style();
/*FITRUE_pay*/
</style>
