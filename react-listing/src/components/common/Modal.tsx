import { useEffect, useCallback, useRef } from 'react';
import { ModalProps } from '../../types/user';
import { KEYBOARD_KEYS, Z_INDEX, ANIMATION } from '../../utils/constants';
import { X } from 'lucide-react';

export const Modal: React.FC<ModalProps> = ({
  isOpen,
  onClose,
  title,
  children,
}) => {
  const modalRef = useRef<HTMLDivElement>(null);
  const previousFocusRef = useRef<HTMLElement | null>(null);

  const handleEscape = useCallback(
    (e: KeyboardEvent) => {
      if (e.key === KEYBOARD_KEYS.ESCAPE) onClose();
    },
    [onClose]
  );

  const handleBackdropClick = (e: React.MouseEvent) => {
    if (e.target === e.currentTarget) onClose();
  };

  useEffect(() => {
    if (isOpen) {
      previousFocusRef.current = document.activeElement as HTMLElement;
      document.addEventListener('keydown', handleEscape);
      document.body.style.overflow = 'hidden';
      setTimeout(() => modalRef.current?.focus(), ANIMATION.MODAL_CLOSE_DELAY);
    }

    return () => {
      document.removeEventListener('keydown', handleEscape);
      document.body.style.overflow = '';
      previousFocusRef.current?.focus();
    };
  }, [isOpen, handleEscape]);

  if (!isOpen) return null;

  return (
    <div
      className={`fixed inset-0 z-${Z_INDEX.MODAL} flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm`}
      onClick={handleBackdropClick}
      role="dialog"
      aria-modal="true"
      aria-labelledby={title ? 'modal-title' : undefined}
    >
      <div
        ref={modalRef}
        className="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
        tabIndex={-1}
      >
        <button
          onClick={onClose}
          className="absolute right-4 top-4 rounded-full p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
          aria-label="Close modal"
        >
          <X className="h-5 w-5" />
        </button>
        {title && (
          <header className="mb-4">
            <h2 id="modal-title" className="text-lg font-semibold text-slate-800">
              {title}
            </h2>
          </header>
        )}
        <div>{children}</div>
      </div>
    </div>
  );
};
