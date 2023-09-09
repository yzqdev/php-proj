module.exports = {
  methods: {
    handleThreadList(data, listKeyName) {
      if (!Array.isArray(data)) return;
      this.hasMore = data.length === this.pageSize;
      const _data = data;
      this.pageNum === 1 && this.threadIdSet.clear();
      for (let i = 0; i < _data.length; i++) {
        if (_data[i]._jv && _data[i]._jv.id) {
          // 如果集合里面有该id，则删除该主题
          if (this.threadIdSet.has(_data[i]._jv.id)) {
            _data.splice(i, 1);
            i--;
          } else {
            this.threadIdSet.add(_data[i]._jv.id);
          }
        }
      }
      this.threadCount
        = (_data
        && _data._jv
        && _data._jv.json
        && _data._jv.json.meta
        && _data._jv.json.meta.threadCount)
        || 0;
      if (this.pageNum === 1) {
        this[listKeyName] = _data;
      } else {
        this[listKeyName] = [...this[listKeyName], ..._data];
      }
      if (_data && _data._jv) {
        this.hasMore = this[listKeyName].length < this.threadCount;
      }
      // 加载成功 页码加1，为加载更多做准备
      this.pageNum += 1;
    }
  }
};
