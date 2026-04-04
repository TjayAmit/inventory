import React from 'react';

export default function AppContentWrapper({ children }: { children: React.ReactNode }) {
    return (
        <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            {children}
        </div>
    );
}