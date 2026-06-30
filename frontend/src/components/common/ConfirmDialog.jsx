import { Button } from './Button'
import { Modal } from './Overlay'

export function ConfirmDialog({ isOpen, onClose, onConfirm, title = 'Are you sure?', message, confirmLabel = 'Confirm', isLoading = false, danger = false }) {
  const footer = <>
  <Button variant="ghost" onClick={onClose}>
    Cancel
  </Button>
    <Button variant={danger ? 'danger' : 'primary'} isLoading={isLoading} onClick={onConfirm}>{confirmLabel}
      </Button></>
  return <Modal isOpen={isOpen} onClose={onClose} title={title} footer={footer}><p className="leading-7 text-charcoal-900/70">{message}</p></Modal>
}
