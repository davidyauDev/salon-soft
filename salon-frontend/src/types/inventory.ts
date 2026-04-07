export interface InventoryItemForm {
  name: string
  type: string
  base_unit: string
  sale_price: string
  stock_minimum: string
  category_id: string
  brand_id: string
  sku: string
  barcode: string
  reorder_point: string
  reorder_qty: string
}

export interface InventoryPurchaseForm {
  item_id: string
  quantity: string
  unit: string
  cost_per_unit: string
  supplier_id: string
  lot_code: string
  invoice_number: string
  expires_at: string
}
