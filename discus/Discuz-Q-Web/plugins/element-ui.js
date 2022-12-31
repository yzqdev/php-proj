import Vue from 'vue';
// import Element from 'element-ui'
import locale from 'element-ui/lib/locale/lang/en';

import {
  Pagination,
  Dialog,
  Autocomplete,
  Dropdown,
  DropdownMenu,
  DropdownItem,
  Menu,
  Submenu,
  MenuItem,
  MenuItemGroup,
  Input,
  InputNumber,
  Radio,
  RadioGroup,
  RadioButton,
  Checkbox,
  CheckboxButton,
  CheckboxGroup,
  Switch,
  Select,
  Option,
  OptionGroup,
  Button,
  ButtonGroup,
  Table,
  TableColumn,
  DatePicker,
  TimeSelect,
  TimePicker,
  Popover,
  Tooltip,
  Breadcrumb,
  BreadcrumbItem,
  Form,
  FormItem,
  Tabs,
  TabPane,
  Tag,
  Tree,
  Alert,
  Slider,
  Icon,
  Row,
  Col,
  Upload,
  Progress,
  Spinner,
  Badge,
  Card,
  Rate,
  Steps,
  Step,
  Carousel,
  CarouselItem,
  Collapse,
  CollapseItem,
  Cascader,
  ColorPicker,
  Transfer,
  Container,
  Header,
  Aside,
  Main,
  Footer,
  Timeline,
  TimelineItem,
  Link,
  Divider,
  Image,
  Calendar,
  Backtop,
  PageHeader,
  CascaderPanel,
  Loading,
  MessageBox,
  Message,
  Notification
} from 'element-ui';

Vue.use(Pagination, { locale });
Vue.use(Dialog, { locale });
Vue.use(Autocomplete, { locale });
Vue.use(Dropdown, { locale });
Vue.use(DropdownMenu, { locale });
Vue.use(DropdownItem, { locale });
Vue.use(Menu, { locale });
Vue.use(Submenu, { locale });
Vue.use(MenuItem, { locale });
Vue.use(MenuItemGroup, { locale });
Vue.use(Input, { locale });
Vue.use(InputNumber, { locale });
Vue.use(Radio, { locale });
Vue.use(RadioGroup, { locale });
Vue.use(RadioButton, { locale });
Vue.use(Checkbox, { locale });
Vue.use(CheckboxButton, { locale });
Vue.use(CheckboxGroup, { locale });
Vue.use(Switch, { locale });
Vue.use(Select, { locale });
Vue.use(Option, { locale });
Vue.use(OptionGroup, { locale });
Vue.use(Button, { locale });
Vue.use(ButtonGroup, { locale });
Vue.use(Table, { locale });
Vue.use(TableColumn, { locale });
Vue.use(DatePicker, { locale });
Vue.use(TimeSelect, { locale });
Vue.use(TimePicker, { locale });
Vue.use(Popover, { locale });
Vue.use(Tooltip, { locale });
Vue.use(Breadcrumb, { locale });
Vue.use(BreadcrumbItem, { locale });
Vue.use(Form, { locale });
Vue.use(FormItem, { locale });
Vue.use(Tabs, { locale });
Vue.use(TabPane, { locale });
Vue.use(Tag, { locale });
Vue.use(Tree, { locale });
Vue.use(Alert, { locale });
Vue.use(Slider, { locale });
Vue.use(Icon, { locale });
Vue.use(Row, { locale });
Vue.use(Col, { locale });
Vue.use(Upload, { locale });
Vue.use(Progress, { locale });
Vue.use(Spinner, { locale });
Vue.use(Badge, { locale });
Vue.use(Card, { locale });
Vue.use(Rate, { locale });
Vue.use(Steps, { locale });
Vue.use(Step, { locale });
Vue.use(Carousel, { locale });
Vue.use(CarouselItem, { locale });
Vue.use(Collapse, { locale });
Vue.use(CollapseItem, { locale });
Vue.use(Cascader, { locale });
Vue.use(ColorPicker, { locale });
Vue.use(Transfer, { locale });
Vue.use(Container, { locale });
Vue.use(Header, { locale });
Vue.use(Aside, { locale });
Vue.use(Main, { locale });
Vue.use(Footer, { locale });
Vue.use(Timeline, { locale });
Vue.use(TimelineItem, { locale });
Vue.use(Link, { locale });
Vue.use(Divider, { locale });
Vue.use(Image, { locale });
Vue.use(Calendar, { locale });
Vue.use(Backtop, { locale });
Vue.use(PageHeader, { locale });
Vue.use(CascaderPanel, { locale });

Vue.use(Loading.directive);

Vue.prototype.$loading = Loading.service;
Vue.prototype.$msgbox = MessageBox;
Vue.prototype.$alert = MessageBox.alert;
Vue.prototype.$confirm = MessageBox.confirm;
Vue.prototype.$prompt = MessageBox.prompt;
Vue.prototype.$notify = Notification;
Vue.prototype.$message = Message;
