<template>
  <div class="site-set-box">
    <Card header="H5图标展示数量：" class="card-radio-con">
      <CardRow description="一行4张图标,一行五张图标">
        <el-radio v-model="imgHangshu" :label="4">一行4张图标</el-radio>
        <el-radio v-model="imgHangshu" :label="5">一行五张图标</el-radio>
        <el-radio v-model="imgHangshu" :label="1">关闭</el-radio>
      </CardRow>
    </Card>

    <div class="kuq-buhuanhang">
      <div class="kuq-kuan-200">
        <Card header="H5图标上传：" >
          <CardRow class="box">
            <div class="avatar-box">
              <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
                v-if="index == 0"
              >
                <el-upload class="avatar-uploader" action :http-request="
                    e => {
                      uploaderLogo(e, index);
                    }
                  " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
                  :before-upload="beforeAvatarUpload"
                >
                  <div v-if="item.imageUrl" class="avatar">
                    <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                        width: 60 + 'px',
                      }" />
                  </div>
                  <i v-else class="el-icon-circle-plus-outline"></i>
                  <p class="avatar-logo">{{ item.text }}</p>
                  <p class="avatar-pm">{{ item.textrule }}</p>
                  <p class="avatar-pm">大小：小于5kb</p>
                </el-upload>
                <el-button v-if="index == 0" type="text" class="avatar-btn" @click="
                    file => {
                      deleteImage(file, index);
                    }
                  ">删除</el-button>
                <!-- <el-dialog :visible.sync="dialogVisible" size="tiny">
                      <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>-->
              </div>
            </div>
          </CardRow>
        </Card>
      </div>
      <div class="kuq-kuan-100">

        <div v-for="(item, index) in htmlapp" :key="index" v-if="index == 0">
          图标{{ index + 1 }}网址
          <br>
          <Card header="" >
            <CardRow description="图标网址">

              <el-input placeholder="网址" v-model="item.url"></el-input>
            </CardRow>
            <br>
            <CardRow description="图标名称">
              <el-input placeholder="站点名称" v-model="item.name"></el-input>
            </CardRow>
            <br>
            <CardRow description="开启">
              <el-radio v-model="item.value" :label="1">关闭</el-radio>
              <el-radio v-model="item.value" :label="2">内部链接</el-radio>
              <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
              <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
			        <el-radio v-model="item.value" :label="5">外部链接</el-radio>
              <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
            </CardRow>
          </Card>
        </div>
      </div>
    </div>

    <div class="kuq-buhuanhang">
      <div class="kuq-kuan-200">
        <Card header="H5图标上传：" >
          <CardRow class="box">
            <div class="avatar-box">
              <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
                v-if="index == 1"
              >
                <el-upload class="avatar-uploader" action :http-request="
                    e => {
                      uploaderLogo(e, index);
                    }
                  " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
                  :before-upload="beforeAvatarUpload"
                >
                  <div v-if="item.imageUrl" class="avatar">
                    <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                        width: 60 + 'px',
                      }" />
                  </div>
                  <i v-else class="el-icon-circle-plus-outline"></i>
                  <p class="avatar-logo">{{ item.text }}</p>
                  <p class="avatar-pm">{{ item.textrule }}</p>
                  <p class="avatar-pm">大小：小于5kb</p>
                </el-upload>
                <el-button v-if="index == 1" type="text" class="avatar-btn" @click="
                    file => {
                      deleteImage(file, index);
                    }
                  ">删除</el-button>
                <!-- <el-dialog :visible.sync="dialogVisible" size="tiny">
                      <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>-->
              </div>
            </div>
          </CardRow>
        </Card>
      </div>
      <div class="kuq-kuan-100">

        <div v-for="(item, index) in htmlapp" :key="index" v-if="index == 1">
          图标2网址
          <br>
          <Card header="" >
            <CardRow description="图标网址">

              <el-input placeholder="网址" v-model="item.url"></el-input>
            </CardRow>
            <br>
            <CardRow description="图标名称">
              <el-input placeholder="站点名称" v-model="item.name"></el-input>
            </CardRow>
            <br>
            <CardRow description="开启">
              <el-radio v-model="item.value" :label="1">关闭</el-radio>
              <el-radio v-model="item.value" :label="2">内部链接</el-radio>
              <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
              <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
              <el-radio v-model="item.value" :label="5">外部链接</el-radio>
              <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
            </CardRow>
          </Card>
        </div>
      </div>
    </div>

    <div class="kuq-buhuanhang">
      <div class="kuq-kuan-200">
        <Card header="H5图标上传：" >
          <CardRow class="box">
            <div class="avatar-box">
              <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
                v-if="index == 2"
              >
                <el-upload class="avatar-uploader" action :http-request="
                    e => {
                      uploaderLogo(e, index);
                    }
                  " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
                  :before-upload="beforeAvatarUpload"
                >
                  <div v-if="item.imageUrl" class="avatar">
                    <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                        width: 60 + 'px',
                      }" />
                  </div>
                  <i v-else class="el-icon-circle-plus-outline"></i>
                  <p class="avatar-logo">{{ item.text }}</p>
                  <p class="avatar-pm">{{ item.textrule }}</p>
                  <p class="avatar-pm">大小：小于5kb</p>
                </el-upload>
                <el-button v-if="index == 2" type="text" class="avatar-btn" @click="
                    file => {
                      deleteImage(file, index);
                    }
                  ">删除</el-button>
                <!-- <el-dialog :visible.sync="dialogVisible" size="tiny">
                      <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>-->
              </div>
            </div>
          </CardRow>
        </Card>
      </div>
      <div class="kuq-kuan-100">

        <div v-for="(item, index) in htmlapp" :key="index" v-if="index == 2">
          图标3网址
          <br>
          <Card header="" >
            <CardRow description="图标网址">

              <el-input placeholder="网址" v-model="item.url"></el-input>
            </CardRow>
            <br>
            <CardRow description="图标名称">
              <el-input placeholder="站点名称" v-model="item.name"></el-input>
            </CardRow>
            <br>
            <CardRow description="开启">
              <el-radio v-model="item.value" :label="1">关闭</el-radio>
              <el-radio v-model="item.value" :label="2">内部链接</el-radio>
              <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
              <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
              <el-radio v-model="item.value" :label="5">外部链接</el-radio>
              <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
            </CardRow>
          </Card>
        </div>
      </div>
    </div>

    <div class="kuq-buhuanhang">
      <div class="kuq-kuan-200">
        <Card header="H5图标上传：" >
          <CardRow class="box">
            <div class="avatar-box">
              <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
                v-if="index == 3"
              >
                <el-upload class="avatar-uploader" action :http-request="
                    e => {
                      uploaderLogo(e, index);
                    }
                  " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
                  :before-upload="beforeAvatarUpload"
                >
                  <div v-if="item.imageUrl" class="avatar">
                    <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                        width: 60 + 'px',
                      }" />
                  </div>
                  <i v-else class="el-icon-circle-plus-outline"></i>
                  <p class="avatar-logo">{{ item.text }}</p>
                  <p class="avatar-pm">{{ item.textrule }}</p>
                  <p class="avatar-pm">大小：小于5kb</p>
                </el-upload>
                <el-button v-if="index == 3" type="text" class="avatar-btn" @click="
                    file => {
                      deleteImage(file, index);
                    }
                  ">删除</el-button>
                <!-- <el-dialog :visible.sync="dialogVisible" size="tiny">
                      <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>-->
              </div>
            </div>
          </CardRow>
        </Card>
      </div>
      <div class="kuq-kuan-100">

        <div v-for="(item, index) in htmlapp" :key="index" v-if="index == 3">
          图标4网址
          <br>
          <Card header="" >
            <CardRow description="图标网址">

              <el-input placeholder="网址" v-model="item.url"></el-input>
            </CardRow>
            <br>
            <CardRow description="图标名称">
              <el-input placeholder="站点名称" v-model="item.name"></el-input>
            </CardRow>
            <br>
            <CardRow description="开启">
              <el-radio v-model="item.value" :label="1">关闭</el-radio>
              <el-radio v-model="item.value" :label="2">内部链接</el-radio>
              <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
              <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
              <el-radio v-model="item.value" :label="5">外部链接</el-radio>
              <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
            </CardRow>
          </Card>
        </div>
      </div>
    </div>

    <div class="kuq-buhuanhang">
      <div class="kuq-kuan-200">
        <Card header="H5图标上传：" >
          <CardRow class="box">
            <div class="avatar-box">
              <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
                v-if="index == 4"
              >
                <el-upload class="avatar-uploader" action :http-request="
                    e => {
                      uploaderLogo(e, index);
                    }
                  " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
                  :before-upload="beforeAvatarUpload"
                >
                  <div v-if="item.imageUrl" class="avatar">
                    <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                        width: 60 + 'px',
                      }" />
                  </div>
                  <i v-else class="el-icon-circle-plus-outline"></i>
                  <p class="avatar-logo">{{ item.text }}</p>
                  <p class="avatar-pm">{{ item.textrule }}</p>
                  <p class="avatar-pm">大小：小于5kb</p>
                </el-upload>
                <el-button v-if="index == 4" type="text" class="avatar-btn" @click="
                    file => {
                      deleteImage(file, index);
                    }
                  ">删除</el-button>
                <!-- <el-dialog :visible.sync="dialogVisible" size="tiny">
                      <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>-->
              </div>
            </div>
          </CardRow>
        </Card>
      </div>
      <div class="kuq-kuan-100">

        <div v-for="(item, index) in htmlapp" :key="index" v-if="index == 4">
          图标5网址
          <br>
          <Card header="" >
            <CardRow description="图标网址">

              <el-input placeholder="网址" v-model="item.url"></el-input>
            </CardRow>
            <br>
            <CardRow description="图标名称">
              <el-input placeholder="站点名称" v-model="item.name"></el-input>
            </CardRow>
            <br>
            <CardRow description="开启">
              <el-radio v-model="item.value" :label="1">关闭</el-radio>
              <el-radio v-model="item.value" :label="2">内部链接</el-radio>
              <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
              <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
              <el-radio v-model="item.value" :label="5">外部链接</el-radio>
              <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
            </CardRow>
          </Card>
        </div>
      </div>
    </div>

    <div class="kuq-buhuanhang">
      <div class="kuq-kuan-200">
        <Card header="H5图标上传：" >
          <CardRow class="box">
            <div class="avatar-box">
              <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
                v-if="index == 5"
              >
                <el-upload class="avatar-uploader" action :http-request="
                    e => {
                      uploaderLogo(e, index);
                    }
                  " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
                  :before-upload="beforeAvatarUpload"
                >
                  <div v-if="item.imageUrl" class="avatar">
                    <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                        width: 60 + 'px',
                      }" />
                  </div>
                  <i v-else class="el-icon-circle-plus-outline"></i>
                  <p class="avatar-logo">{{ item.text }}</p>
                  <p class="avatar-pm">{{ item.textrule }}</p>
                  <p class="avatar-pm">大小：小于5kb</p>
                </el-upload>
                <el-button v-if="index == 5" type="text" class="avatar-btn" @click="
                    file => {
                      deleteImage(file, index);
                    }
                  ">删除</el-button>
                <!-- <el-dialog :visible.sync="dialogVisible" size="tiny">
                      <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>-->
              </div>
            </div>
          </CardRow>
        </Card>
      </div>
      <div class="kuq-kuan-100">

        <div v-for="(item, index) in htmlapp" :key="index" v-if="index == 5">
          图标6网址
          <br>
          <Card header="" >
            <CardRow description="图标网址">

              <el-input placeholder="网址" v-model="item.url"></el-input>
            </CardRow>
            <br>
            <CardRow description="图标名称">
              <el-input placeholder="站点名称" v-model="item.name"></el-input>
            </CardRow>
            <br>
            <CardRow description="开启">
              <el-radio v-model="item.value" :label="1">关闭</el-radio>
              <el-radio v-model="item.value" :label="2">内部链接</el-radio>
              <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
              <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
              <el-radio v-model="item.value" :label="5">外部链接</el-radio>
              <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
            </CardRow>
          </Card>
        </div>
      </div>
    </div>

    <div class="kuq-buhuanhang">
      <div class="kuq-kuan-200">
        <Card header="H5图标上传：" >
          <CardRow class="box">
            <div class="avatar-box">
              <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
                v-if="index == 6"
              >
                <el-upload class="avatar-uploader" action :http-request="
                    e => {
                      uploaderLogo(e, index);
                    }
                  " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
                  :before-upload="beforeAvatarUpload"
                >
                  <div v-if="item.imageUrl" class="avatar">
                    <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                        width: 60 + 'px',
                      }" />
                  </div>
                  <i v-else class="el-icon-circle-plus-outline"></i>
                  <p class="avatar-logo">{{ item.text }}</p>
                  <p class="avatar-pm">{{ item.textrule }}</p>
                  <p class="avatar-pm">大小：小于5kb</p>
                </el-upload>
                <el-button v-if="index == 6" type="text" class="avatar-btn" @click="
                    file => {
                      deleteImage(file, index);
                    }
                  ">删除</el-button>
                <!-- <el-dialog :visible.sync="dialogVisible" size="tiny">
                      <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>-->
              </div>
            </div>
          </CardRow>
        </Card>
      </div>
      <div class="kuq-kuan-100">

        <div v-for="(item, index) in htmlapp" :key="index" v-if="index == 6">
          图标7网址
          <br>
          <Card header="" >
            <CardRow description="图标网址">

              <el-input placeholder="网址" v-model="item.url"></el-input>
            </CardRow>
            <br>
            <CardRow description="图标名称">
              <el-input placeholder="站点名称" v-model="item.name"></el-input>
            </CardRow>
            <br>
            <CardRow description="开启">
              <el-radio v-model="item.value" :label="1">关闭</el-radio>
              <el-radio v-model="item.value" :label="2">内部链接</el-radio>
              <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
              <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
              <el-radio v-model="item.value" :label="5">外部链接</el-radio>
              <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
            </CardRow>
          </Card>
        </div>
      </div>
    </div>

    <div class="kuq-buhuanhang">
      <div class="kuq-kuan-200">
        <Card header="H5图标上传：" >
          <CardRow class="box">
            <div class="avatar-box">
              <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
                v-if="index == 7"
              >
                <el-upload class="avatar-uploader" action :http-request="
                    e => {
                      uploaderLogo(e, index);
                    }
                  " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
                  :before-upload="beforeAvatarUpload"
                >
                  <div v-if="item.imageUrl" class="avatar">
                    <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                        width: 60 + 'px',
                      }" />
                  </div>
                  <i v-else class="el-icon-circle-plus-outline"></i>
                  <p class="avatar-logo">{{ item.text }}</p>
                  <p class="avatar-pm">{{ item.textrule }}</p>
                  <p class="avatar-pm">大小：小于5kb</p>
                </el-upload>
                <el-button v-if="index == 7" type="text" class="avatar-btn" @click="
                    file => {
                      deleteImage(file, index);
                    }
                  ">删除</el-button>
                <!-- <el-dialog :visible.sync="dialogVisible" size="tiny">
                      <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>-->
              </div>
            </div>
          </CardRow>
        </Card>
      </div>
      <div class="kuq-kuan-100">

        <div v-for="(item, index) in htmlapp" :key="index" v-if="index == 7">
          图标8网址
          <br>
          <Card header="" >
            <CardRow description="图标网址">

              <el-input placeholder="网址" v-model="item.url"></el-input>
            </CardRow>
            <br>
            <CardRow description="图标名称">
              <el-input placeholder="站点名称" v-model="item.name"></el-input>
            </CardRow>
            <br>
            <CardRow description="开启">
              <el-radio v-model="item.value" :label="1">关闭</el-radio>
              <el-radio v-model="item.value" :label="2">内部链接</el-radio>
              <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
              <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
              <el-radio v-model="item.value" :label="5">外部链接</el-radio>
              <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
            </CardRow>
          </Card>
        </div>
      </div>
    </div>

    <div class="kuq-buhuanhang">
      <div class="kuq-kuan-200">
        <Card header="H5图标上传：" >
          <CardRow class="box">
            <div class="avatar-box">
              <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
                v-if="index == 8"
              >
                <el-upload class="avatar-uploader" action :http-request="
                    e => {
                      uploaderLogo(e, index);
                    }
                  " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
                  :before-upload="beforeAvatarUpload"
                >
                  <div v-if="item.imageUrl" class="avatar">
                    <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                        width: 60 + 'px',
                      }" />
                  </div>
                  <i v-else class="el-icon-circle-plus-outline"></i>
                  <p class="avatar-logo">{{ item.text }}</p>
                  <p class="avatar-pm">{{ item.textrule }}</p>
                  <p class="avatar-pm">大小：小于5kb</p>
                </el-upload>
                <el-button v-if="index == 8" type="text" class="avatar-btn" @click="
                    file => {
                      deleteImage(file, index);
                    }
                  ">删除</el-button>
                <!-- <el-dialog :visible.sync="dialogVisible" size="tiny">
                      <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>-->
              </div>
            </div>
          </CardRow>
        </Card>
      </div>
      <div class="kuq-kuan-100">

        <div v-for="(item, index) in htmlapp" :key="index" v-if="index == 8">
          图标9网址
          <br>
          <Card header="" >
            <CardRow description="图标网址">

              <el-input placeholder="网址" v-model="item.url"></el-input>
            </CardRow>
            <br>
            <CardRow description="图标名称">
              <el-input placeholder="站点名称" v-model="item.name"></el-input>
            </CardRow>
            <br>
            <CardRow description="开启">
              <el-radio v-model="item.value" :label="1">关闭</el-radio>
              <el-radio v-model="item.value" :label="2">内部链接</el-radio>
              <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
              <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
              <el-radio v-model="item.value" :label="5">外部链接</el-radio>
              <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
            </CardRow>
          </Card>
        </div>
      </div>
    </div>

    <div class="kuq-buhuanhang">
      <div class="kuq-kuan-200">
        <Card header="H5图标上传：" >
          <CardRow class="box">
            <div class="avatar-box">
              <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
                v-if="index == 9"
              >
                <el-upload class="avatar-uploader" action :http-request="
                    e => {
                      uploaderLogo(e, index);
                    }
                  " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
                  :before-upload="beforeAvatarUpload"
                >
                  <div v-if="item.imageUrl" class="avatar">
                    <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                        width: 60 + 'px',
                      }" />
                  </div>
                  <i v-else class="el-icon-circle-plus-outline"></i>
                  <p class="avatar-logo">{{ item.text }}</p>
                  <p class="avatar-pm">{{ item.textrule }}</p>
                  <p class="avatar-pm">大小：小于5kb</p>
                </el-upload>
                <el-button v-if="index == 9" type="text" class="avatar-btn" @click="
                    file => {
                      deleteImage(file, index);
                    }
                  ">删除</el-button>
                <!-- <el-dialog :visible.sync="dialogVisible" size="tiny">
                      <img width="100%" :src="dialogImageUrl" alt="">
                  </el-dialog>-->
              </div>
            </div>
          </CardRow>
        </Card>
      </div>
      <div class="kuq-kuan-100">

        <div v-for="(item, index) in htmlapp" :key="index" v-if="index == 9">
          图标10网址
          <br>
          <Card header="" >
            <CardRow description="图标网址">

              <el-input placeholder="网址" v-model="item.url"></el-input>
            </CardRow>
            <br>
            <CardRow description="图标名称">
              <el-input placeholder="站点名称" v-model="item.name"></el-input>
            </CardRow>
            <br>
            <CardRow description="开启">
              <el-radio v-model="item.value" :label="1">关闭</el-radio>
              <el-radio v-model="item.value" :label="2">内部链接</el-radio>
              <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
              <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
              <el-radio v-model="item.value" :label="5">外部链接</el-radio>
              <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
            </CardRow>
          </Card>
        </div>
      </div>
    </div>

    <!--Card header="H5图标上传：">
      <CardRow class="box">
        <div class="avatar-box">
          <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
            v-if="index <= 4"
          >
            <el-upload class="avatar-uploader" action :http-request="
                e => {
                  uploaderLogo(e, index);
                }
              " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
              :before-upload="beforeAvatarUpload"
            >
              <div v-if="item.imageUrl" class="avatar">
                <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                    width: 140 + 'px',
                  }" />
              </div>
              <i v-else class="el-icon-circle-plus-outline"></i>
              <p class="avatar-logo">{{ item.text }}</p>
              <p class="avatar-pm">{{ item.textrule }}</p>
              <p class="avatar-pm">大小：小于5kb</p>
            </el-upload>
            <el-button v-if="index <= 4" type="text" class="avatar-btn" @click="
                file => {
                  deleteImage(file, index);
                }
              ">删除</el-button>

          </div>
        </div>
      </CardRow>
    </Card-->


    <!--Card header="H5图标上传：">
      <CardRow class="box">
        <div class="avatar-box">
          <div class="avatar-somer-box" v-for="(item, index) in numberimgapp" :key="index"
            v-if="index >= 5"
          >
            <el-upload class="avatar-uploader" action :http-request="
                e => {
                  uploaderLogo(e, index);
                }
              " :show-file-list="false" :on-success="handleAvatarSuccess" @change="handleFile"
              :before-upload="beforeAvatarUpload"
            >
              <div v-if="item.imageUrl" class="avatar">
                <img :src="item.imageUrl" class="avatar-LogoImage" :alt="item.text" :title="item.text" :style="{
                    width: 140 + 'px',
                  }" />
              </div>
              <i v-else class="el-icon-circle-plus-outline"></i>
              <p class="avatar-logo">{{ item.text }}</p>
              <p class="avatar-pm">{{ item.textrule }}</p>
              <p class="avatar-pm">大小：小于5kb</p>
            </el-upload>
            <el-button v-if="index >= 5" type="text" class="avatar-btn" @click="
                file => {
                  deleteImage(file, index);
                }
              ">删除</el-button>
-
          </div>
        </div>
      </CardRow>
    </Card-->
    <!--div v-for="(item, index) in htmlapp" :key="index">
      图标{{ index + 1 }}网址
      <br>
      <Card header="" >
        <CardRow description="图标网址">

          <el-input placeholder="网址" v-model="item.url"></el-input>
        </CardRow>
        <br>
        <CardRow description="图标名称">
          <el-input placeholder="站点名称" v-model="item.name"></el-input>
        </CardRow>
        <br>
        <CardRow description="开启">
          <el-radio v-model="item.value" :label="1">关闭</el-radio>
          <el-radio v-model="item.value" :label="2">内部链接</el-radio>
          <el-radio v-model="item.value" :label="3">公众号链接</el-radio>
          <el-radio v-model="item.value" :label="4">小程序链接</el-radio>
          <a href="https://discuzq.cc/thread/183" target="_blank">教程</a>
        </CardRow>
      </Card>
    </div-->

    <!--Card header="关闭首页提问：" class="card-radio-con">
      <CardRow description="关闭首页提问">
        <el-radio v-model="ask[0] && ask[0].value" :label="1">开启首页提问</el-radio>
        <el-radio v-model="ask[0] && ask[0].value" :label="2">关闭</el-radio>
      </CardRow>
    </Card-->



    <!-- <Card header="权限购买">
      <CardRow class="card-pays" description="开启后，用户角色将可以配置价格和有效期，用户可在前台进行用户角色购买">
        <el-switch v-model="purchase" active-color="#336699" inactive-color="#bbbbbb">
        </el-switch>
      </CardRow>
    </Card> -->




    <Card class="footer-btn">
      <el-button type="primary" size="medium" @click="siteSetPost">提交</el-button>
    </Card>
  </div>
</template>
<style>
  .disabled .el-upload--picture-card {
    display: none;
  }
  .kuq-buhuanhang {
    display: flex;
    display: -webkit-box;
  }
  .kuq-kuan-200 {
    width: 12.33333rem;
  }
  .kuq-kuan-100 {
    width: 100%;
    margin-top: 3.33333rem;
  }

</style>
<script>
  import "../../../scss/site/module/globalStyle.scss";
  import siteTubiaoCon from "../../../controllers/site/global/siteTubiaoCon";

  export default {
    name: "site-tubiao-view",
    ...siteTubiaoCon
  };

</script>
