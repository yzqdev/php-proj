export default {
  data() {
    return {
      scrollTop: 0
    };
  },
  mounted() {
    this.addEventListener(window, 'scroll', this.handleScroll);
  },
  destroyed() {
    this.removeEventListener(window, 'scroll', this.handleScroll);
  },
  methods: {
    // 监听滚动事件
    handleScroll() {
      // 变量scrollTop是滚动条滚动时，距离顶部的距离
      const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
      // 变量windowHeight是可视区的高度
      const windowHeight = document.documentElement.clientHeight || document.body.clientHeight;
      // 变量scrollHeight是滚动条的总高度
      const scrollHeight = document.documentElement.scrollHeight || document.body.scrollHeight;
      // 滚动条到底部的条件
      // 部分设备scrollTop + windowHeight 的值会出现小数点，所以不能直接等于
      // scrollTop + windowHeight 的值可能一直小于scrollHeiht导致scrollLoadMore不执行给他加大下数值
      this.scrollData();
      if (Math.ceil(scrollHeight - windowHeight) >= 20) {
        this.scrollPost();
      }
      if (Math.ceil(scrollTop + windowHeight + 10) >= 200) {
        this.scrollLoadMore(); // 加载的列表数据
      }
      /*
      if (Math.ceil(scrollTop + windowHeight + 10) >= scrollHeight) {
        this.scrollLoadMore(); // 加载的列表数据
      }
      */
      this.scrollTop = scrollTop;
    },
    // 添加事件监听器的兼容
    addEventListener(obj, event, fn, boo) {
      if (obj.addEventListener) {
        obj.addEventListener(event, fn, boo);
      } else if (obj.attachEvent) {
        obj.attachEvent(`on${event}`, fn);
      }
    },
    // 移除事件监听器的兼容
    removeEventListener(obj, event, fn, boo) {
      if (obj.removeEventListener) {
        obj.removeEventListener(event, fn, boo);
      } else if (obj.detachEvent) {
        obj.detachEvent(`on${event}`, fn);
      }
    }
  }
};
