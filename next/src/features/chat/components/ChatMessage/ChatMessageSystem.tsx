'use client'

export const ChatMessageSystem = ({ text, time }: { text: string; time: string }) => {
    return (
        <div className="my-2 flex justify-center">
            <div className="rounded-md bg-muted px-3 py-1 text-center text-xs text-muted-foreground">
                <div>{text}</div>
                <div className="mt-0.5 text-[10px]">{time}</div>
            </div>
        </div>
    )
}
