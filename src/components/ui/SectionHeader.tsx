import { cn } from "@/lib/utils";

interface SectionHeaderProps {
  index?: string;
  label: string;
  title: string;
  description?: string;
  align?: "left" | "center";
  className?: string;
  titleClassName?: string;
}

export function SectionHeader({
  index,
  label,
  title,
  description,
  align = "left",
  className,
  titleClassName,
}: SectionHeaderProps) {
  const centered = align === "center";

  return (
    <header
      className={cn(
        "max-w-3xl",
        centered && "mx-auto text-center",
        className
      )}
    >
      <div
        className={cn(
          "flex items-center gap-4",
          centered && "justify-center"
        )}
      >
        {index && (
          <span className="font-display text-3xl leading-none text-elomiah-gold/40 md:text-4xl">
            {index}
          </span>
        )}
        <div className={cn("flex flex-col gap-2", index && "pt-1")}>
          <p className="section-label">{label}</p>
          <div className="gold-rule" />
        </div>
      </div>
      <h2
        className={cn(
          "heading-display mt-6 text-4xl md:text-5xl lg:text-[3.25rem]",
          titleClassName
        )}
      >
        {title}
      </h2>
      {description && (
        <p
          className={cn(
            "mt-5 max-w-xl text-base leading-relaxed text-elomiah-muted md:text-[17px]",
            centered && "mx-auto"
          )}
        >
          {description}
        </p>
      )}
    </header>
  );
}
