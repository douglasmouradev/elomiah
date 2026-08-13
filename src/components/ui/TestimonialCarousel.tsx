"use client";

import Image from "next/image";
import { useEffect, useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { ArrowLeft, ArrowRight } from "lucide-react";
import { Testimonial } from "@/lib/types";
import { InitialsAvatar } from "@/components/ui/InitialsAvatar";

interface TestimonialCarouselProps {
  testimonials: Testimonial[];
}

export function TestimonialCarousel({ testimonials }: TestimonialCarouselProps) {
  const [index, setIndex] = useState(0);

  useEffect(() => {
    if (testimonials.length <= 1) return;
    const id = setInterval(() => {
      setIndex((i) => (i + 1) % testimonials.length);
    }, 7000);
    return () => clearInterval(id);
  }, [testimonials.length]);

  if (!testimonials.length) return null;

  const current = testimonials[index];

  return (
    <div className="relative mx-auto max-w-4xl">
      <div className="overflow-hidden px-2 py-4 md:px-8">
        <AnimatePresence mode="wait">
          <motion.div
            key={current.id}
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -8 }}
            transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
            className="grid gap-10 md:grid-cols-[auto_1fr] md:items-start md:gap-12"
          >
            <div className="relative mx-auto h-16 w-16 shrink-0 overflow-hidden md:mx-0 md:h-20 md:w-20">
              {current.photo ? (
                <Image
                  src={current.photo}
                  alt={current.name}
                  fill
                  className="object-cover grayscale-[20%]"
                  sizes="80px"
                />
              ) : (
                <InitialsAvatar
                  name={current.name}
                  className="h-full w-full text-xl md:text-2xl"
                />
              )}
            </div>
            <div>
              <blockquote className="font-display text-2xl leading-[1.45] text-elomiah-green md:text-3xl lg:text-[2rem]">
                &ldquo;{current.text}&rdquo;
              </blockquote>
              <footer className="mt-8 flex items-center gap-4">
                <div className="gold-rule w-8" />
                <p className="text-sm text-elomiah-muted">
                  <span className="font-medium text-elomiah-green">
                    {current.name}
                  </span>
                  {current.role && (
                    <span className="text-elomiah-muted/70"> · {current.role}</span>
                  )}
                </p>
              </footer>
            </div>
          </motion.div>
        </AnimatePresence>
      </div>

      {testimonials.length > 1 && (
        <div className="mt-10 flex items-center justify-center gap-6">
          <button
            type="button"
            aria-label="Anterior"
            onClick={() =>
              setIndex((i) => (i - 1 + testimonials.length) % testimonials.length)
            }
            className="text-elomiah-muted transition-colors hover:text-elomiah-green"
          >
            <ArrowLeft size={18} strokeWidth={1.25} />
          </button>
          <div className="flex gap-3">
            {testimonials.map((t, i) => (
              <button
                key={t.id}
                type="button"
                aria-label={`Depoimento ${i + 1}`}
                onClick={() => setIndex(i)}
                className={`h-px transition-all duration-300 ${
                  i === index
                    ? "w-8 bg-elomiah-green"
                    : "w-4 bg-elomiah-green/20 hover:bg-elomiah-green/40"
                }`}
              />
            ))}
          </div>
          <button
            type="button"
            aria-label="Próximo"
            onClick={() => setIndex((i) => (i + 1) % testimonials.length)}
            className="text-elomiah-muted transition-colors hover:text-elomiah-green"
          >
            <ArrowRight size={18} strokeWidth={1.25} />
          </button>
        </div>
      )}
    </div>
  );
}
