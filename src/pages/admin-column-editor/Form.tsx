// Inside Item.tsx

import React, { FC, useState, useEffect } from 'react'
import { Item } from '@interfaces/admin-column-editor'

interface ItemProps {
  item: Item
  onSave: (updatedItem: Item) => void
  onClose: () => void
}

const Item: FC<ItemProps> = ({ item, onSave, onClose }) => {
  const [form, setForm] = useState<Item>(item)

  // Update the form state when the item prop changes
  useEffect(() => {
    setForm(item)
  }, [item])

  const handleInputChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>,
  ) => {
    const { name, value } = e.target
    setForm((prevState) => ({
      ...prevState,
      [name]: value,
    }))
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    onSave(form)
  }

  const i18n = ozopanel.i18n

  return (
    <div className="popup-overlay">
      <div className="popup-content">
        <div className="flex items-center justify-between rounded-t border-b p-3 dark:border-gray-600">
          <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
            {i18n.editColumn}
          </h3>
          <button
            type="button"
            className="end-2.5 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
            data-modal-hide="authentication-modal"
            onClick={onClose}
          >
            <svg
              className="h-3 w-3"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 14 14"
            >
              <path
                stroke="currentColor"
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
              />
            </svg>
          </button>
        </div>
        <form onSubmit={handleSubmit} className="p-5">
          <div className="mb-3">
            <label
              htmlFor="label"
              className="mb-2 block text-sm font-medium text-gray-900 dark:text-white"
            >
              Label:
            </label>
            <input
              type="text"
              id="label"
              name="label"
              value={form.label}
              onChange={handleInputChange}
              className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
            />
          </div>

          <div className="mb-3">
            <label
              htmlFor="type"
              className="mb-2 block text-sm font-medium text-gray-900 dark:text-white"
            >
              Type:
            </label>
            <input
              type="text"
              id="type"
              name="type"
              value={form.type}
              onChange={handleInputChange}
              className="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
            />
          </div>

          <div className="mb-3 grid grid-cols-1">
            <div className="col">
              <label
                htmlFor="width"
                className="mb-2 block text-sm font-medium text-gray-900 dark:text-white"
              >
                Width:
              </label>
              <input
                type="text"
                id="width"
                name="width"
                size={3}
                value={form.width}
                onChange={handleInputChange}
                className="rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
              />
              <select
                id="width_unit"
                name="width_unit"
                value={form.width_unit}
                onChange={handleInputChange}
              >
                <option value="%">%</option>
                <option value="px">px</option>
              </select>
            </div>
          </div>

          <button
            type="submit"
            className="rounded-lg bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-gradient-to-br focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800"
          >
            {i18n.apply}
          </button>
        </form>
      </div>
    </div>
  )
}

export default Item
