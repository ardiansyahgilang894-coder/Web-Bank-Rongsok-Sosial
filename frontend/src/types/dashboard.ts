export interface Summary {
  total_penjualan_rongsok: number
  total_berat_rongsok: number
  total_pemasukan: number
  total_pengeluaran: number
  saldo_kas: number
  total_galeri: number
}

declare module 'vue-router' {
  interface RouteMeta {
    roles?: string[]
  }
}