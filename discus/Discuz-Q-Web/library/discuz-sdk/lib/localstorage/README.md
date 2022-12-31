# 本地存储 - LocalBridge

## 使用

```javascript
import LocalBridge from '@discuz/sdk/lib/localstorage';

const ls = new LocalBridge();
ls.set('user', 'angelzou');
ls.get('user'); // angelzou
ls.remove('user');
ls.get('user'); // null
```

1. 初始化的时候可以设置键的前缀

```javascript
const ls = new LocalBridge({ prefix: 'test' })
```

可以初始化的值：
- prefix: string（前缀）；默认值：`__dzqls-`

2. 方法

- 设置存储项

  ```javascript
  ls.set('user', 'angelzou', 30); // 设置 user 选项的值为 angelzou，30 秒后过期
  ```

  入参：
  - key: string（键）
  - value: string | object（值）
  - expires: number（可选：设置过期时间，单位秒）

- 获取存储项

  ```javascript
  ls.get('user');
  ```

  入参：
  - key: string（键）

  返回：如果有存储的值，返回存储的值，如果没有，返回 null

- 删除存储想

  ```javascript
  ls.remove('user');
  ```

  入参：
  - key: string（键）

- 清空所有存储项

  ```javascript
  ls.clear();
  ```