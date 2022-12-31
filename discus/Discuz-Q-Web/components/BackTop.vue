<template>
  <el-backtop class="back-top-container" :right="20" :bottom="89">
    <div class="back-top">
      <svg-icon type="arrow-top" class="icon" />
    </div>
  </el-backtop>
</template>

<style lang='scss' scoped>
.back-top-container{
  @media (min-width:1200px){
    left: 50%;
    right: auto;
    margin-left: 525px;
  }
  @media (max-width:1199px){
    left: auto;
    margin: auto;
  }
  .back-top{
    height: 40px;
    width: 40px;
    background-color: white;
    box-shadow: 0px 3px 3px rgba(0, 0, 0, 0.1);
    text-align: center;
    line-height: 35px;
    border-radius: 2px;
    transition: all .3s;
    .icon{
      font-size:16px;
      color: #666;
    }
    /*IFTRUE_pay*/
    &:hover{
      box-shadow: 0px 2px 5px 0 rgba(0, 0, 0, 0.2);
      .icon{
        color: rgba(47, 133, 244, 0.8);
      }
    }
    /*FITRUE_pay*/
  }
}

</style>
