<template>
  <div class="global">
    <div class="block">
      <div class="text">{{ $t("post.isPay") }}:</div>
      <el-select
        size="medium"
        :value="payment.paidType"
        placeholder="请选择"
        :popper-append-to-body="false"
        popper-class="selectinfo"
        @change="value => $emit('paymentChange', { key: 'paidType', value })"
      >
        <el-option
          v-for="item in options"
          :key="item.value"
          :label="item.label"
          :value="item.value"
        />
      </el-select>
    </div>
    <div class="block">
      <div v-show="payment.paidType === 'paid'">
        <div class="text">{{ $t("post.paymentAmount") }}:</div>
        <el-input
          v-model="price"
          size="medium"
          placeholder="0"
          maxlength="7"
          @change="
            $emit('paymentChange', { key: 'price', value: parseFloat(price) })
          "
          @input="onPriceInput"
        >
          <span slot="prefix" class="prefix">￥</span>
        </el-input>
      </div>
      <div v-show="payment.paidType === 'attachmentPaid' && type === 1">
        <div class="text">{{ $t("post.paymentAmount") }}:</div>
        <el-input
          v-model="attachmentPrice"
          size="medium"
          placeholder="0"
          maxlength="7"
          @change="
            $emit('paymentChange', {
              key: 'attachmentPrice',
              value: parseFloat(attachmentPrice)
            })
          "
          @input="onAttachmentPriceInput"
        >
          <span slot="prefix" class="prefix">￥</span>
        </el-input>
      </div>
    </div>
    <div class="block">
      <div v-show="payment.paidType === 'paid' && type === 1">
        <div class="text">{{ $t("post.freeWord") }}:</div>
        <el-select
          size="medium"
          :value="freeWords"
          :popper-append-to-body="false"
          popper-class="selectinfo"
          @change="
            value => $emit('paymentChange', { key: 'freeWords', value: value })
          "
        >
          <el-option
            v-for="item in freeWordsOptions"
            :key="item.value"
            :label="item.label"
            :value="item.value"
          />
        </el-select>
      </div>
    </div>
  </div>
</template>

<script>
import formatAmount from '@/utils/formatAmount';
export default {
  name: 'EditorPayment',
  props: {
    payment: {
      type: Object,
      default: () => {}
    },
    type: {
      type: Number,
      default: 0
    },
    canUploadAttachments: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      freeWords: 0,
      price: 0,
      attachmentPrice: 0,
      options: [
        {
          value: 'free',
          label: this.$t('post.freeWatch')
        },
        {
          value: 'paid',
          label: this.$t('post.paidWatch')
        }
      ],
      freeWordsOptions: [
        { value: 0, label: '0%' },
        { value: 0.1, label: '10%' },
        { value: 0.3, label: '30%' },
        { value: 0.5, label: '50%' },
        { value: 0.7, label: '70%' },
        { value: 1, label: '100%' }
      ]
    };
  },
  watch: {
    payment: {
      handler(val) {
        this.freeWords = val.freeWords || 0;
        this.price = val.price || 0;
        this.attachmentPrice = val.attachmentPrice || 0;
      },
      immediate: true,
      deep: true
    },
    type: {
      handler(val) {
        val === 1 && this.canUploadAttachments
          ? this.options.push({
            value: 'attachmentPaid',
            label: this.$t('post.postFreeAttachmentPaid')
          })
          : '';
      },
      immediate: true
    }
  },
  methods: {
    onPriceInput(value) {
      this.price = formatAmount(value);
    },
    onAttachmentPriceInput(value) {
      this.attachmentPrice = formatAmount(value);
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/variable/color.scss";
/*IFTRUE_pay*/
@import '@/assets/css/variable/mixin.scss';
@include custom-el-select-style();
@include custom-el-input-style();
/*FITRUE_pay*/
$font-color: #6d6d6d;
$height: 35px;
.global {
  width: 100%;
  display: flex;
  margin-top: 20px;

  > .block {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-left: 22px;
    font-size: 14px;

    &:first-child {
      margin-left: 0;
    }

    .prefix {
      line-height: 38px;
      font-size: 16px;
      color: $font-color;
    }

    .text {
      font-weight: bold;
      color: $font-color;
      margin-bottom: 10px;
    }
  }
}

::v-deep.el-input {
  background: $background-color-editor;
  > input {
    background: $background-color-editor;
  }
}

::v-deep.el-select {
  > .el-input {
    > input {
      background: $background-color-editor;
    }
  }
}

/*IFTRUE_default*/
::v-deep .el-input .el-input__inner:focus {
  border-color: #409EFF;
}
/*FITRUE_default*/
</style>
