import { FC, useState } from 'react'
import { UseAlert } from '@/components/alert/Provider'
import { DropdownRow } from '@/components/dropdown'

interface Item {
  id: string
  type: string
  label: string
  width: string
  width_unit: string
}

interface ItemsProps {
  items: Item[]
  onChange: (items: Item[]) => void
  onSelect: (i: number) => void
  onDelete: (i: number) => void
}

const Items: FC<ItemsProps> = ({ items, onChange, onSelect, onDelete }) => {
  const [draggedIndex, setDraggedIndex] = useState<number | null>(null)

  const { delConfirm /* , proAlert */ } = UseAlert()

  const handleDragStart = (i: number) => {
    setDraggedIndex(i)
  }

  const handleDragOver = (i: number) => {
    if (draggedIndex === null) return
    if (i === draggedIndex) return

    const reorderedItems = Array.from(items)
    const [draggedItem] = reorderedItems.splice(draggedIndex, 1)
    reorderedItems.splice(i, 0, draggedItem)
    onChange(reorderedItems)
    setDraggedIndex(i)
  }

  const handleDeleteClick = (i: number) => {
    // proAlert()
    delConfirm(() => {
      onDelete(i)
    })
  }

  const i18n = ozopanel.i18n
  return (
    <div className="">
      {items.map((item, i) => (
        <div
          key={item.id}
          className="ozop-shortable-item"
          style={{
            display: 'flex',
            justifyContent: 'space-between'
          }}
          draggable
          onDragStart={() => handleDragStart(i)}
          onDragOver={() => handleDragOver(i)}
        >
          <div dangerouslySetInnerHTML={{ __html: item.label }} />
          <DropdownRow>
            <li>
              <button
                className="block w-full whitespace-nowrap bg-transparent px-4 py-2 text-sm font-normal text-neutral-700 hover:bg-neutral-100 active:text-neutral-800 active:no-underline disabled:pointer-events-none disabled:bg-transparent disabled:text-neutral-400 dark:text-neutral-200 dark:hover:bg-neutral-600"
                onClick={() => onSelect(i)}
              >
                {i18n.edit}
              </button>
              <button
                className="block w-full whitespace-nowrap bg-transparent px-4 py-2 text-sm font-normal text-neutral-700 hover:bg-neutral-100 active:text-neutral-800 active:no-underline disabled:pointer-events-none disabled:bg-transparent disabled:text-neutral-400 dark:text-neutral-200 dark:hover:bg-neutral-600"
                onClick={() => handleDeleteClick(i)}
              >
                {i18n.del}
              </button>
            </li>
          </DropdownRow>
        </div>
      ))}
    </div>
  )
}

export default Items
