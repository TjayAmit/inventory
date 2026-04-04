import * as React from "react";
import { X } from "lucide-react";
import { cn } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";

export interface Option {
    value: string;
    label: string;
}

interface AutoSuggestionProps {
    options: Option[];
    selected: string[];
    onChange: (selected: string[]) => void;
    placeholder?: string;
    className?: string;
}

export function AutoSuggestion({
    options,
    selected,
    onChange,
    placeholder = "Type to search...",
    className,
}: AutoSuggestionProps) {
    const [searchQuery, setSearchQuery] = React.useState("");
    const [isOpen, setIsOpen] = React.useState(false);
    const inputRef = React.useRef<HTMLInputElement>(null);
    const containerRef = React.useRef<HTMLDivElement>(null);

    const handleUnselect = (value: string) => {
        onChange(selected.filter((s) => s !== value));
    };

    const handleSelect = (value: string) => {
        if (!selected.includes(value)) {
            onChange([...selected, value]);
        }
        setSearchQuery("");
        inputRef.current?.focus();
    };

    // Filter out already selected and match search
    const availableOptions = options.filter(
        (option) =>
            !selected.includes(option.value) &&
            option.label.toLowerCase().includes(searchQuery.toLowerCase())
    );

    // Close on click outside
    React.useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (
                containerRef.current &&
                !containerRef.current.contains(event.target as Node)
            ) {
                setIsOpen(false);
            }
        };
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    return (
        <div ref={containerRef} className={cn("relative", className)}>
            {/* Selected badges + Input */}
            <div
                onClick={() => {
                    setIsOpen(true);
                    inputRef.current?.focus();
                }}
                className={cn(
                    "min-h-[40px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm",
                    "flex flex-wrap items-center gap-1 cursor-text",
                    "focus-within:outline-none focus-within:ring-2 focus-within:ring-ring"
                )}
            >
                {selected.map((value) => {
                    const option = options.find((o) => o.value === value);
                    return (
                        <Badge
                            key={value}
                            variant="outline"
                            className="shrink-0 bg-primary/15 text-primary-foreground border-primary/50 hover:bg-primary/25"
                        >
                            {option?.label || value}
                            <button
                                type="button"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleUnselect(value);
                                    inputRef.current?.focus();
                                }}
                                className="ml-1 p-0.5 rounded-full outline-none focus:ring-2 focus:ring-ring cursor-pointer hover:bg-primary/30 hover:text-white"
                            >
                                <X className="h-3 w-3" />
                            </button>
                        </Badge>
                    );
                })}
                <input
                    ref={inputRef}
                    type="text"
                    value={searchQuery}
                    onChange={(e) => {
                        setSearchQuery(e.target.value);
                        setIsOpen(true);
                    }}
                    onFocus={() => setIsOpen(true)}
                    placeholder={selected.length === 0 ? placeholder : ""}
                    className="flex-1 bg-transparent outline-none min-w-[80px] placeholder:text-muted-foreground"
                />
            </div>

            {/* Suggestions dropdown */}
            {isOpen && (
                <div className="absolute z-50 w-full mt-1 rounded-md border bg-popover shadow-md">
                    <div className="max-h-[200px] overflow-y-auto p-1">
                        {availableOptions.length === 0 ? (
                            <div className="px-2 py-3 text-sm text-muted-foreground text-center">
                                {searchQuery
                                    ? "No matching options."
                                    : selected.length === options.length
                                    ? "All items selected."
                                    : "Type to search..."}
                            </div>
                        ) : (
                            availableOptions.map((option) => (
                                <button
                                    key={option.value}
                                    type="button"
                                    onClick={() => handleSelect(option.value)}
                                    className="w-full px-2 py-2 text-sm text-left rounded-sm hover:bg-accent hover:text-accent-foreground cursor-pointer"
                                >
                                    {option.label}
                                </button>
                            ))
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
