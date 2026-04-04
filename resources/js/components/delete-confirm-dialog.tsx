import React from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Trash2 } from 'lucide-react';

interface DeleteConfirmDialogProps {
    isOpen: boolean;
    onClose: () => void;
    onConfirm: () => void;
    title?: string;
    description?: string;
    itemName?: string;
}

export function DeleteConfirmDialog({
    isOpen,
    onClose,
    onConfirm,
    title = 'Delete Confirmation',
    description = 'This action cannot be undone.',
    itemName,
}: DeleteConfirmDialogProps) {
    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2 text-destructive">
                        <Trash2 className="w-5 h-5" />
                        {title}
                    </DialogTitle>
                    <DialogDescription className="pt-2">
                        {itemName ? (
                            <>
                                Are you sure you want to delete <strong>{itemName}</strong>?
                                <br />
                                {description}
                            </>
                        ) : (
                            description
                        )}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter className="gap-2 pt-4">
                    <Button variant="outline" onClick={onClose}>
                        Cancel
                    </Button>
                    <Button variant="destructive" onClick={onConfirm}>
                        Proceed Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
