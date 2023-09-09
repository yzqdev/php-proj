<template>
  <div>
    <el-dialog
      title="请选择"
      width="25%"
      :visible.sync="onFire"
      :before-close="close"
      style="text-align: center"
    >
      <el-button plain @click="showInPic = true">插入图片</el-button>
      <el-button plain @click="showInvideo = true">插入视频</el-button>
      <el-button plain @click="showInaudio = true">插入音频</el-button>
      <el-button plain @click="showRecorder = true">录制音频</el-button>
    </el-dialog>

    <el-dialog
      title="粘贴图片网址"
      :visible.sync="showInPic"
      width="30%"
    >
      <el-input v-model="urlimg" placeholder="粘贴图片网址" />
      <span slot="footer" class="dialog-footer">
        <el-button @click="showInPic = false">取 消</el-button>
        <el-button type="primary" @click="$emit('inedit',{ key: 'pic', value: urlimg })">确 定</el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="录音"
      width="30%"
      :visible.sync="showRecorder"
    >
      <el-tooltip class="item" effect="dark" content="开始录制" placement="top">
        <el-button class="iconfont icon-Recordermicrophonesound" circle @click="StartRecorder" />
      </el-tooltip>
      <el-tooltip class="item" effect="dark" content="停止录制" placement="top">
        <el-button type="danger" class="iconfont icon-stopcircle" circle @click="StopRecorder" />
      </el-tooltip>
      <el-tooltip class="item" effect="dark" content="播放音频" placement="top">
        <el-button type="success" class="iconfont icon-play" circle @click="StartPlayRecorder" />
      </el-tooltip>
      <el-tooltip class="item" effect="dark" content="停止播放音频" placement="top">
        <el-button type="danger" class="iconfont icon-stopcircle" circle @click="StopPlayRecorder" />
      </el-tooltip>
      <el-tooltip class="item" effect="dark" content="下载音频" placement="top">
        <el-button class="iconfont icon-down3" circle @click="DownRecorder" />
      </el-tooltip>
      <el-tooltip class="item" effect="dark" content="插入音频" placement="top">
        <el-button class="iconfont icon-insert" circle @click="getMp3Data()" />
      </el-tooltip>
      <el-divider v-show="Reaing"></el-divider>
      <div v-show="Reaing">
        <span>录音时长(秒):{{duration}}</span>
        <el-divider direction="vertical"></el-divider>
        <span>录音大小(字节):{{fileSize}}</span>
        <el-divider direction="vertical"></el-divider>
        <span>录音音量百分比(%):{{vol}}</span>
      </div>
    </el-dialog>

    <el-dialog
      title="粘贴音频网址"
      :visible.sync="showInaudio"
      width="30%"
    >
      <el-input v-model="wavurl" placeholder="粘贴音频网址" />
      <span slot="footer" class="dialog-footer">
        <el-button @click="showInaudio = false">取 消</el-button>
        <el-button type="primary" @click="$emit('inedit',{ key: 'audio', value: wavurl })">确 定</el-button>
      </span>
    </el-dialog>

    <el-dialog
      title="粘贴视频网址"
      :visible.sync="showInvideo"
      width="30%"
    >
      <el-input v-model="vurl" placeholder="粘贴视频网址" />
      <span slot="footer" class="dialog-footer">
        <el-button @click="showInvideo = false">取 消</el-button>
        <el-button type="primary" @click="$emit('inedit',{ key: 'video', value: vurl })">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'Fire',
  props: {
    onFire: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      urlimg: '',
      wavurl: '',
      vurl: '',
      filename: '',
      file: null,
      duration: 0,
      fileSize: 0,
      vol: 0,
      showInPic: false,
      showInaudio: false,
      showRecorder: false,
      Reaing: false,
      showInvideo: false
    };
  },
  mounted() {
    console.log(this.onFire);
    this.filename = this.makefilename('5');
    const _this = this;
    this.$recorder.onprogress = function(params) {
      _this.duration = params.duration;
      _this.fileSize = params.fileSize;
      _this.vol = params.vol;
      console.log('录音时长(秒)', params.duration);
      console.log('录音大小(字节)', params.fileSize);
      console.log('录音音量百分比(%)', params.vol);
      // console.log('当前录音的总数据([DataView, DataView...])', params.data);
    };
  },
  methods: {
    makefilename(length) {
      const data
        = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F',
          'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y',
          'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r',
          's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
      let nums = '';
      for (let i = 0; i < length; i++) {
        const r = parseInt(Math.random() * 61, 10);
        nums += data[r];
      }
      return nums;
    },
    // 将blob转换为file
    blobToFile(theBlob, fileName) {
      // eslint-disable-next-line no-param-reassign
      theBlob.lastModifiedDate = new Date();
      // eslint-disable-next-line no-param-reassign
      theBlob.name = fileName;
      return theBlob;
    },
    getMp3Data() {
      if (!this.Reaing) {
        return this.$message.warning('请先录音~');
      }
      const mp3Blob = this.convertToMp3(this.$recorder.getWAV());
      // const file = this.blobToFile(mp3Blob, this.filename);
      const res = {
        filename: this.filename,
        file: mp3Blob
      };
      this.$emit('inedit', { key: 'recorder', value: res });
      // this.$recorder.download(mp3Blob, 'recorder', 'mp3');
    },
    StartRecorder() {
      this.duration = 0;
      this.fileSize = 0;
      this.vol = 0;
      const _this = this;
      this.$recorder.start().then(() => {
        // 开始录音
        _this.Reaing = true;
        console.log(`开始录音`);
      }, (error) => {
        // 出错了
        console.log(`${error.name} : ${error.message}`);
      });
    },
    StopRecorder() {
      if (!this.Reaing) {
        return this.$message.warning('请先录音~');
      }
      this.$recorder.stop();
    },
    StartPlayRecorder() {
      if (!this.Reaing) {
        return this.$message.warning('请先录音~');
      }
      this.$recorder.play();
    },
    StopPlayRecorder() {
      if (!this.Reaing) {
        return this.$message.warning('请先录音~');
      }
      this.$recorder.stopPlay();
    },
    DownRecorder() {
      if (!this.Reaing) {
        return this.$message.warning('请先录音~');
      }
      this.$recorder.downloadWAV([this.filename]);
    },
    convertToMp3(wavDataView) {
      // 获取wav头信息
      const wav = this.$lamejs.WavHeader.readHeader(wavDataView); // 此处其实可以不用去读wav头信息，毕竟有对应的config配置
      const { channels, sampleRate } = wav;
      const mp3enc = new this.$lamejs.Mp3Encoder(channels, sampleRate, 128);
      // 获取左右通道数据
      const result = this.$recorder.getChannelData();
      const buffer = [];

      const leftData = result.left && new Int16Array(result.left.buffer, 0, result.left.byteLength / 2);
      const rightData = result.right && new Int16Array(result.right.buffer, 0, result.right.byteLength / 2);
      const remaining = leftData.length + (rightData ? rightData.length : 0);

      const maxSamples = 1152;
      for (let i = 0; i < remaining; i += maxSamples) {
        const left = leftData.subarray(i, i + maxSamples);
        let right = null;
        let mp3buf = null;

        if (channels === 2) {
          right = rightData.subarray(i, i + maxSamples);
          mp3buf = mp3enc.encodeBuffer(left, right);
        } else {
          mp3buf = mp3enc.encodeBuffer(left);
        }

        if (mp3buf.length > 0) {
          buffer.push(mp3buf);
        }
      }

      const enc = mp3enc.flush();

      if (enc.length > 0) {
        buffer.push(enc);
      }

      return new Blob(buffer, { type: 'audio/mp3' });
    },
    close() {
      this.$emit('close');
    }
  }
};
</script>

<style lang="scss" scoped>
@import "@/assets/css/iconfont.scss";

</style>
