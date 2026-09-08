import api from '@/utils/request'
import type { ApiResponse, PaginatedResponse, User, UserCreate, UserUpdate } from '@/types'

export function getUsers(params?: { page?: number; pageSize?: number }): Promise<ApiResponse<PaginatedResponse<User>>> {
  return api.get('/users', { params }).then((res) => res.data)
}

export function getUser(id: number): Promise<ApiResponse<User>> {
  return api.get(`/users/${id}`).then((res) => res.data)
}

export function createUser(data: UserCreate): Promise<ApiResponse<User>> {
  return api.post('/users', data).then((res) => res.data)
}

export function updateUser(id: number, data: UserUpdate): Promise<ApiResponse<User>> {
  return api.put(`/users/${id}`, data).then((res) => res.data)
}

export function deleteUser(id: number): Promise<ApiResponse<null>> {
  return api.delete(`/users/${id}`).then((res) => res.data)
}
