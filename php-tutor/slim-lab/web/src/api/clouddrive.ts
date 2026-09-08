import request from '@/utils/request'
import type {
  ApiResponse,
  CloudDriveDir,
  CloudDriveShare,
  SearchResult,
} from '@/types/api'

export function clouddriveLogin(pwd: string) {
  const formData = new FormData()
  formData.append('pwd', pwd)
  return request.post<ApiResponse<{ login: boolean }>>(
    '/clouddrive?action=login',
    formData,
  )
}

export function clouddriveLogout() {
  return request.get<ApiResponse<{ login: boolean }>>(
    '/clouddrive?action=logout',
  )
}

export function clouddriveCheck() {
  return request.get<ApiResponse<{ login: boolean }>>(
    '/clouddrive?action=check',
  )
}

export function clouddriveListDir(dir = '', sortby = '', sortorder = '') {
  return request.get<ApiResponse<CloudDriveDir>>('/clouddrive', {
    params: { action: 'list_dir', dir, sortby, sortorder },
  })
}

export function clouddriveMkdir(currentFolder: string, targetDir: string) {
  const formData = new FormData()
  formData.append('current_folder', currentFolder)
  formData.append('target_dir', targetDir)
  return request.post<ApiResponse<null>>('/clouddrive?action=mkdir', formData)
}

export function clouddriveUpload(
  currentFolder: string,
  file: File,
  relativePath = '',
) {
  const formData = new FormData()
  formData.append('current_folder', currentFolder)
  formData.append('file', file)
  if (relativePath) formData.append('relative_path', relativePath)
  return request.post<ApiResponse<null>>('/clouddrive?action=upload', formData, {
    timeout: 300000,
  })
}

export function clouddriveDelete(currentFolder: string, name: string) {
  const formData = new FormData()
  formData.append('current_folder', currentFolder)
  formData.append('name', name)
  return request.post<ApiResponse<null>>('/clouddrive?action=delete', formData)
}

export function clouddriveBatchDelete(currentFolder: string, batchList: string[]) {
  const formData = new FormData()
  formData.append('current_folder', currentFolder)
  batchList.forEach((item) => formData.append('batch_list[]', item))
  return request.post<ApiResponse<null>>(
    '/clouddrive?action=batch_delete',
    formData,
  )
}

export function clouddriveRename(
  currentFolder: string,
  oldName: string,
  newName: string,
) {
  const formData = new FormData()
  formData.append('current_folder', currentFolder)
  formData.append('old_name', oldName)
  formData.append('new_name', newName)
  return request.post<ApiResponse<null>>('/clouddrive?action=rename', formData)
}

export function clouddriveMove(
  currentFolder: string,
  srcName: string,
  dstDir: string,
) {
  const formData = new FormData()
  formData.append('current_folder', currentFolder)
  formData.append('src_name', srcName)
  formData.append('dst_dir', dstDir)
  return request.post<ApiResponse<null>>('/clouddrive?action=move', formData)
}

export function clouddriveSearch(kw: string) {
  const formData = new FormData()
  formData.append('kw', kw)
  return request.post<ApiResponse<{ list: SearchResult[] }>>(
    '/clouddrive?action=global_search',
    formData,
  )
}

export function clouddriveCreateShare(params: {
  current_folder: string
  share_file: string
  is_dir_share: string
  share_pwd: string
  share_expire: string
}) {
  const formData = new FormData()
  Object.entries(params).forEach(([key, value]) => formData.append(key, value))
  return request.post<
    ApiResponse<{ url: string; pwd: string; token: string }>
  >('/clouddrive?action=create_share', formData)
}

export function clouddriveGetShareList() {
  return request.get<ApiResponse<{ list: CloudDriveShare[] }>>(
    '/clouddrive?action=get_share_list',
  )
}

export function clouddriveDeleteShare(token: string) {
  const formData = new FormData()
  formData.append('token', token)
  return request.post<ApiResponse<null>>(
    '/clouddrive?action=delete_share',
    formData,
  )
}

/** ---- 分享访客接口（免登录，token + 提取密码即凭证） ---- */

export interface ShareInfo {
  need_pwd: boolean
  name?: string
  is_dir: boolean
  expire_text: string
}

export function shareInfo(token: string, sharePwd: string) {
  const formData = new FormData()
  formData.append('token', token)
  formData.append('share_pwd', sharePwd)
  return request.post<ApiResponse<ShareInfo>>(
    '/clouddrive?action=share_info',
    formData,
  )
}

export function getShareDownloadUrl(token: string, sharePwd: string) {
  return `/api/clouddrive?action=share_download&token=${encodeURIComponent(token)}&share_pwd=${encodeURIComponent(sharePwd)}`
}

export function getShareZipUrl(token: string, sharePwd: string) {
  return `/api/clouddrive?action=share_zip&token=${encodeURIComponent(token)}&share_pwd=${encodeURIComponent(sharePwd)}`
}

export function getShareStreamUrl(token: string, sharePwd: string) {
  return `/api/clouddrive?action=share_stream&token=${encodeURIComponent(token)}&share_pwd=${encodeURIComponent(sharePwd)}`
}

export function clouddriveChangePwd(newPwd1: string, newPwd2: string) {
  const formData = new FormData()
  formData.append('new_pwd1', newPwd1)
  formData.append('new_pwd2', newPwd2)
  return request.post<ApiResponse<null>>(
    '/clouddrive?action=change_pwd',
    formData,
  )
}

export function getStreamUrl(file: string) {
  return `/api/clouddrive?action=stream&file=${encodeURIComponent(file)}`
}

export function getDownloadUrl(dir: string, file: string) {
  return `/api/clouddrive?action=download&dir=${encodeURIComponent(dir)}&file=${encodeURIComponent(file)}`
}

export function getZipUrl(dir: string, folder: string) {
  return `/api/clouddrive?action=zip&dir=${encodeURIComponent(dir)}&folder=${encodeURIComponent(folder)}`
}

/** 分享链接：前端访客页路由（后端仅存 token 元数据） */
export function buildShareUrl(token: string) {
  return `${window.location.origin}/share/${token}`
}
