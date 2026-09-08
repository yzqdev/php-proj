# Request
> 基于 `axios` 的请求库
> axios：[https://github.com/axios/axios](https://github.com/axios/axios)

## 请求配置
> 同 axios
[https://github.com/axios/axios#request-config](https://github.com/axios/axios#request-config)

增加两个额外的针对于参数校验的配置

- `isValidate`：是否校验请求参数，为 `true` 时表示校验，反之不校验
- `validateRules`: 校验规则，具体相关配置请看：[async-validator](https://github.com/yiminghe/async-validator)。实例化请求实例的时候这个参数无效，需要针对请求接口单独进行配置。

## 响应模式
> 同 axios
[https://github.com/axios/axios#response-schema](https://github.com/axios/axios#response-schema)

## 使用方法
> 同 axios
[https://github.com/axios/axios#config-defaults](https://github.com/axios/axios#config-defaults)

## 请求入参校验
### 使用
- 请求入口的请求拦截最后加上校验参数的方法

  ```javascript
  import Request from '@discuz/sdk/lib/request';
  import { validateParameter } from '@discuz/sdk/lib/request/validator';

  // 初始化请求实例
  const http = Request.create();
  // 如果想要校验参数，实例化的时候进行校验值的传入
  // const http = Request.create({ isValidate: true });

  // 请求拦截，后进先出，所以参数校验就可以放到最后，这样就是最新进行拦截
  // 设置请求头
  http.interceptors.request.use(setRequestHeader);
  // 校验参数处理
  http.interceptors.request.use(validateParameter);
  ```

- 接口参数校验

  ```javascript
  const validateRules = {
    include: {
      type: 'number', // 参数类型
      required: true, // 是否必须
    },
    name: {
      type: 'string',
      required: true,
    },
  };

  export const getForum = async () => {
    try {
      const result = await http.get('https://discuz-dev.dnspod.dev/api/forum', { params: {}, validateRules });
    } catch (error) {
      console.log(error);
    }
  };
  ```
