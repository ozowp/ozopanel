import { FC, useState } from 'react'
import { useDelConfirm } from '@/components/alert/delete/Provider'
import './style.scss'

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

  const { openDelConfirm } = useDelConfirm()

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
    openDelConfirm(() => {
      onDelete(i)
    })
  }

  const i18n = ozopanel.i18n
  return (
    <ul className="ozop-sortable-list">
      {items.map((item, i) => (
        <li
          key={item.id}
          className="ozop-shortable-item"
          draggable
          onDragStart={() => handleDragStart(i)}
          onDragOver={() => handleDragOver(i)}
          onClick={() => onSelect(i)}
        >
          <span dangerouslySetInnerHTML={{ __html: item.label }} />
          <button
            className="ml-2 text-red-500"
            onClick={(e) => {
              e.stopPropagation() // Prevent the click event from triggering the parent li click event
              handleDeleteClick(i)
            }}
          >
            {i18n.del}
          </button>
        </li>
      ))}
    </ul>
  )
}

export default Items
