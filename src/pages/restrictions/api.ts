import api from '@utils/api'

export const getData = async (type: string) => {
  const res = await api.get(`restrictions/${type}`)
  if (res.success) {
    return res.data
  } else {
    throw res.data.map((value: string) => new Error(value))
  }
}

export const delData = async (type: string, selectedItems: string[]) => {
  const res = await api.del(`restrictions/${type}`, selectedItems.join(','))
  if (res.success) {
    return res.data
  } else {
    throw res.data.map((value: string) => new Error(value))
  }
}
