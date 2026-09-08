module.exports = {
  methods: {
    handleThreadList(Data, data, listKeyName) {
      if (!Array.isArray(data)) return;
      this.newData = Data;
      this.hasMore = Data.currentPage < Data.totalPage;
      const _data = [...data];
      this.pageNum === 1 && this.threadIdSet.clear();
      for (let i = 0; i < _data.length; i++) {
        if (_data[i] && _data[i].thread.pid) {
          // 如果集合里面有该id，则删除该主题
          if (this.threadIdSet.has(_data[i].thread.pid)) {
            _data.splice(i, 1);
            i--;
          } else {
            this.threadIdSet.add(_data[i].thread.pid);
          }
        }
      }
      this.threadCount = Data.totalCount || 0;
      if (this.pageNum === 1) {
        this[listKeyName] = _data;
      } else {
        this[listKeyName] = [...this[listKeyName], ..._data];
      }
      if (Data) {
        this.hasMore = Data.currentPage < Data.totalPage;
      }
      // 加载成功 页码加1，为加载更多做准备
      this.pageNum += 1;
    }
  }
};
