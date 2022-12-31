<template>
  <div class="loading-container">
    {{ $t('discuzq.list.loading') }}...
  </div>
</template>
<script>
export default {
  name: 'Loading',
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    fontSize: {
      type: Number,
      default: 32
    }
  }
};
</script>
<style lang="scss" scoped>
.loading-container{
  text-align: center;
  padding:30px 0;
  color: #8590A6;
  /*IFTRUE_pay*/ 
  color: #1878f3;
  /*FITRUE_pay*/ 
  font-size: 14px;
}
</style>
