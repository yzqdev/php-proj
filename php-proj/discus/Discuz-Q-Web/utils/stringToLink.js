const stringToLink = str => {
  let _str = str;
  let count = 0; // 标记链接
  const arr = []; // 存储链接的匹配标记、链接地址、链接内容

  // 1 匹配a标签
  const reg1 = /<a href="([^"\s]+)"(\stitle="(\S*)")?>([^<>]+)<\/a>/g;
  // 2 匹配img标签
  const reg2 = /<img[^<>]+src="[^"\s<>]*"[^<>]+>/g;
  // 3 匹配文本内链接
  const reg3 = /https?:\/\/(?:[\w-]+\.)+[a-z0-9]+((?:\/[\w-]*)+(\.[\w-]*)?)?(\?[^#\s{}]+)?(#\S+)?/gi;

  // 提取链接信息，并返回提取后的字符串
  _str = _str
    .replace(reg1, function(...arg) {
      count++;
      arr.push({
        type: 'a',
        mark: `{{a${count}}}`,
        link: arg[1],
        title: arg[3] || '链接',
        con: arg[4]
      });
      return `{{a${count}}}`;
    })
    .replace(reg2, function(...arg) {
      count++;
      arr.push({
        type: 'img',
        mark: `{{img${count}}}`,
        con: arg[0]
      });
      return `{{img${count}}}`;
    })
    .replace(reg3, function(...arg) {
      count++;
      arr.push({
        type: 'a',
        mark: `{{a${count}}}`,
        link: arg[0],
        title: '链接',
        con: arg[0]
      });
      return `{{a${count}}}`;
    });

  // 遍历信息数组，替换真实链接
  arr.forEach(item => {
    _str = _str.replace(item.mark, function() {
      if (item.type === 'img') return item.con;

      if (/^(https?)/.test(item.link)) {
        return `
          <a href="${item.link}" target="_blank" title="${item.title}" rel="noopener noreferrer">
            ${item.con}
          </a>
        `;
      } else {
        return `
          <a href="https://${item.link}" target="_blank" title="${item.title}" rel="noopener noreferrer">
            ${item.con}
          </a>
        `;
      }
    });
  });

  return _str;
};

export default stringToLink;

