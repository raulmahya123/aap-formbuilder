// Keep shared dialogs mutually exclusive, including detail-to-edit transitions.
export function openDialog(dialog) {
    if (!(dialog instanceof HTMLDialogElement)) return false;
    const others = [...document.querySelectorAll('dialog.ui-modal[open]')].filter(item => item !== dialog);
    for (const other of others) {
        const cancel = new Event('cancel', { cancelable: true });
        if (!other.dispatchEvent(cancel)) return false;
    }
    others.forEach(other => other.close());
    if (!dialog.open) dialog.showModal();
    return true;
}
